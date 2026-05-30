<?php

namespace App\Http\Controllers;

use App\Models\EquipmentRequest;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HRController extends Controller
{
    /**
     * Show HR dashboard
     */
    public function dashboard()
    {
        // Get counts for stats
        $totalRequests = EquipmentRequest::where('created_by', Auth::id())->count();
        $pending = EquipmentRequest::where('created_by', Auth::id())->where('status', 'en_attente')->count();
        $inProgress = EquipmentRequest::where('created_by', Auth::id())->where('status', 'en_cours')->count();
        $completed = EquipmentRequest::where('created_by', Auth::id())->where('status', 'termine')->count();

        return view('hr.dashboard', compact('totalRequests', 'pending', 'inProgress', 'completed'));
    }

    /**
     * Show create request FORM
     */
    public function create()
    {
        return view('hr.create');
    }

    /**
     * Store new equipment request
     */
    public function store(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'employee_name' => 'required|string|max:255',
            'department' => 'required|string',
            'position' => 'required|in:employe,apprenti,stagiaire',
        ], [
            'employee_name.required' => 'Le nom de l\'employé est requis.',
            'department.required' => 'Le département est requis.',
            'position.required' => 'Le poste est requis.',
        ]);

        // ⏳Create the request
        $equipmentRequest = EquipmentRequest::create([
            'employee_name' => $validated['employee_name'],
            'department' => $validated['department'],
            'position' => $validated['position'],
            'status' => 'en_attente',
            'created_by' => Auth::id(),
        ]);

        //🔔 Create notification for IT Manager
        $itManager = User::where('role', 'it_manager')->first();

        if ($itManager) {
            Notification::create([
                'user_id' => $itManager->id,
                'type' => 'new_request',
                'message' => "Nouvelle demande pour {$equipmentRequest->employee_name} ({$equipmentRequest->department})",
                'request_id' => $equipmentRequest->id,
            ]);
        }

        return redirect()->route('hr.requests')->with('success', 'Demande créée avec succès!');
    }





  public function requests()
{
    // Get all requests created by this HR user
    $requests = EquipmentRequest::where('created_by', Auth::id())
        ->orderBy('created_at', 'asc')
        ->get();

    // 🗑️ Delete "request_completed" NOTIFICATION when HR VIEWS their requests
    // (Same logic as IT Manager and Technician)
    Notification::where('user_id', Auth::id())
        ->where('type', 'request_completed')
        ->delete();

    return view('hr.requests', compact('requests'));
}




    /**
     *🗑️ Delete a request (only if status is "en_attente")
     */
    public function destroy($id)
    {
        $request = EquipmentRequest::findOrFail($id);

        // Check if this HR user created this request
        if ($request->created_by !== Auth::id()) {
            return redirect()->route('hr.requests')->with('error', 'Vous n\'êtes pas autorisé à supprimer cette demande.');
        }

        // Check if status is "en_attente"
        if ($request->status !== 'en_attente' && $request->status !== 'termine') {
            return redirect()->route('hr.requests')->with('error', 'Impossible de supprimer une demande en cours ou terminée.');
        }

        // Delete the request (notifications will be deleted automatically due to cascade)
        $request->delete();

        return redirect()->route('hr.requests')->with('success', 'Demande supprimée avec succès!');
    }



    /**
     * 🔔Aaffichié //Show notifications
     */
    public function notifications()
    {
       /** @var User $user */
        $user = Auth::user();

        $notifications = $user->notifications()->orderBy('created_at', 'desc')->get();
        return view('hr.notifications', compact('notifications'));
    }


    /**

     * @param int $id - The ID of the request to edit
     */
    public function edit($id)
    {
        // Find the request or show 404 error
        $request = EquipmentRequest::findOrFail($id);

        // Security check: Make sure this HR user created this request
        if ($request->created_by !== Auth::id()) {
            return redirect()->route('hr.requests')->with('error', 'Vous n\'êtes pas autorisé à modifier cette demande.');
        }

        // Business rule: Can only edit if status is "en_attente"
        if ($request->status !== 'en_attente') {
            return redirect()->route('hr.requests')->with('error', 'Impossible de modifier une demande en cours ou terminée.');
        }

        // Show the edit form with the request data
        return view('hr.edit', compact('request'));
    }

    /**

     * @param Request $request - The HTTP request with form data
     * @param int $id - The ID of the request to update
     */
    public function update(Request $request, $id)
    {
        // Find the equipment request
        $equipmentRequest = EquipmentRequest::findOrFail($id);

        // Security check: Make sure this HR user owns this request
        if ($equipmentRequest->created_by !== Auth::id()) {
            return redirect()->route('hr.requests')->with('error', 'Vous n\'êtes pas autorisé à modifier cette demande.');
        }

        //  Can only update if status is "en_attente"
        if ($equipmentRequest->status !== 'en_attente') {
            return redirect()->route('hr.requests')->with('error', 'Impossible de modifier une demande en cours ou terminée.');
        }

        // Validate the input (same validation as create)
        $validated = $request->validate([
            'employee_name' => 'required|string|max:255',
            'department' => 'required|string',
            'position' => 'required|in:employe,apprenti,stagiaire',
        ], [
            'employee_name.required' => 'Le nom de l\'employé est requis.',
            'department.required' => 'Le département est requis.',
            'position.required' => 'Le poste est requis.',
        ]);

        // Update the request with new data
        $equipmentRequest->update([
            'employee_name' => $validated['employee_name'],
            'department' => $validated['department'],
            'position' => $validated['position'],
        ]);

        // Redirect back with success message
        return redirect()->route('hr.requests')->with('success', 'Demande modifiée avec succès!');
    }


    /**
 * Mark all HR notifications as read (delete them)
 */
public function clearNotifications()
{
    // Delete all completed request notifications for this HR user
    Notification::where('user_id', Auth::id())
        ->where('type', 'request_completed')
        ->delete();

    return redirect()->back()->with('success', 'Notifications effacées!');
}
}
