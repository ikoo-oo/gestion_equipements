<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\EquipmentRequest;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TechnicianController extends Controller
{
    /**
     * Afficher le tableau de bord du technicien
     */
    public function dashboard()
    {
        // Récupérer l'ID du technicien connecté
        $technicianId = Auth::id();

        // Compter les demandes assignées à ce technicien avec statut "en_cours"
        $inProgress = EquipmentRequest::where('assigned_to', $technicianId)
            ->where('status', 'en_cours')
            ->count();

        // Compter les demandes terminées ce mois par ce technicien
        $completedThisMonth = EquipmentRequest::where('assigned_to', $technicianId)
            ->where('status', 'termine')
            ->whereMonth('updated_at', now()->month) // Filtrer par mois actuel
            ->count();

        // Compter le total de toutes les demandes assignées à ce technicien
        $totalAssigned = EquipmentRequest::where('assigned_to', $technicianId)->count();

        // Envoyer les statistiques à la vue
        return view('technician.dashboard', compact('inProgress', 'completedThisMonth', 'totalAssigned'));
    }




    public function requests()
    {
        // Récupérer toutes les demandes assignées à ce technicien

        $requests = EquipmentRequest::where('assigned_to', Auth::id())
            ->with('creator')
            ->orderBy('created_at', 'asc')
            ->get();

        // Envoyer les demandes à la vue
        return view('technician.requests', compact('requests'));
    }

    /**
     * Afficher les détails d'une demande spécifique
     *
     * @param int $id - L'ID de la demande à afficher

     */
    public function details($id)
    {
        // Trouver la demande par ID
        $request = EquipmentRequest::with(['creator', 'technician'])
            ->findOrFail($id);

        // Vérifier que cette demande est bien assignée à CE technicien

        if ($request->assigned_to !== Auth::id()) {
            // Si la demande n'est pas assignée à ce technicien, rediriger avec erreur
            return redirect()->route('technician.requests')
                ->with('error', 'Vous n\'êtes pas autorisé à voir cette demande.');
        }

        // Tout est OK, afficher les détails
        return view('technician.details', compact('request'));
    }








    /**
     * Marquer une demande comme terminée
     *
     * @param int $id - L'ID de la demande à terminer
     */
 public function complete($id)
{
    $equipmentRequest = EquipmentRequest::findOrFail($id);

    if ($equipmentRequest->assigned_to !== Auth::id()) {
        return redirect()->route('technician.requests')
            ->with('error', 'Vous n\'êtes pas autorisé à modifier cette demande.');
    }

    if ($equipmentRequest->status !== 'en_cours') {
        return redirect()->route('technician.requests')
            ->with('error', 'Cette demande ne peut pas être marquée comme terminée.');
    }

    $equipmentRequest->update([
        'status' => 'termine'
    ]);

    // 🔔Create notification for HR
    Notification::create([
        'user_id' => $equipmentRequest->created_by,
        'type' => 'request_completed',
        'message' => "Demande terminée pour {$equipmentRequest->employee_name} - Équipements livrés",
        'request_id' => $equipmentRequest->id,
    ]);

    // 🗑️ DELETE old notifications for this request
    // Delete "new_request" (IT Manager) and "assigned_request" (Technician)
    Notification::where('request_id', $equipmentRequest->id)
        ->whereIn('type', ['new_request', 'assigned_request'])
        ->delete();

    return redirect()->route('technician.requests')
        ->with('success', 'Demande marquée comme terminée! L\'utilisateur RH a été notifié.');
}



     public function notifications()
    {
        // 🔔Affichié // Récupérer toutes les notifications pour le technicien connecté
      /** @var User $user */
$user = Auth::user();

$notifications = $user->notifications()
    ->orderBy('created_at', 'desc')
    ->get();


        // Envoyer les notifications à la vue
        return view('technician.notifications', compact('notifications'));
    }
}
