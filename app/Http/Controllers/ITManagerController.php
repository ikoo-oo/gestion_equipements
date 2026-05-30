<?php

namespace App\Http\Controllers;

use App\Models\EquipmentRequest;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ITManagerController extends Controller
{
    /**
     * Show IT Manager dashboard
     */
    public function dashboard()
    {
        $newRequests = EquipmentRequest::where('status', 'en_attente')->count();
        $inProgress = EquipmentRequest::where('status', 'en_cours')->count();
        $completedThisMonth = EquipmentRequest::where('status', 'termine')
            ->whereMonth('updated_at', now()->month)
            ->count();

        return view('it-manager.dashboard', compact('newRequests', 'inProgress', 'completedThisMonth'));
    }

    /**
     * Show all requests received from HR (status = "en_attente")
     */
    public function received()
    {
        $requests = EquipmentRequest::where('status', 'en_attente')
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('it-manager.received', compact('requests'));
    }

    /**
     * Show the assignment form with prefilled HR data
     */
    public function showAssign($id)
    {
        $request = EquipmentRequest::findOrFail($id);
        $technicians = User::where('role', 'technician')->get();

        return view('it-manager.assign', compact('request', 'technicians'));
    }

    /**
     * Process the assignment form
     */
    public function assign(Request $request, $id)
    {
        $equipmentRequest = EquipmentRequest::findOrFail($id);

        $validated = $request->validate([
            'equipment_description' => 'required|string',
            'deadline' => 'nullable|date',
            'assigned_to' => 'required|exists:users,id',
        ], [
            'equipment_description.required' => 'La description des équipements est requise.',
            'assigned_to.required' => 'Vous devez sélectionner un technicien.',
            'assigned_to.exists' => 'Le technicien sélectionné n\'existe pas.',
        ]);

        $equipmentRequest->update([
            'equipment_description' => $validated['equipment_description'],
            'deadline' => $validated['deadline'],
            'assigned_to' => $validated['assigned_to'],
            'status' => 'en_cours',
        ]);








        //🔔 Create notification for technician
        Notification::create([
            'user_id' => $validated['assigned_to'],
            'type' => 'assigned_request',
            'message' => "Nouvelle demande assignée : {$equipmentRequest->employee_name} - {$equipmentRequest->equipment_description}",
            'request_id' => $equipmentRequest->id,
        ]);

        // 🗑️ DELETE the "new_request" notification for IT Manager
        Notification::where('request_id', $equipmentRequest->id)
            ->where('type', 'new_request')
            ->delete();

        return redirect()->route('it-manager.dashboard')->with('success', 'Demande assignée avec succès au technicien!');
    }







    /**
     * Show all requests that have been assigned to technicians

     */
    public function assigned()
    {
        // Get all requests that have a technician assigned
        $requests = EquipmentRequest::whereNotNull('assigned_to')
            ->with(['creator', 'technician'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('it-manager.assigned', compact('requests'));
    }







    /**
     * 🔔Aaffichié  //Show notifications for IT Manager
     */
    public function notifications()
    {
      /** @var User $user */
        $user = Auth::user();

        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('it-manager.notifications', compact('notifications'));
    }
}
