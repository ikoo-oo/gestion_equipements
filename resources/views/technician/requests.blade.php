@extends('layouts.app')

@section('title', 'Mes demandes')

@section('content')
<div class="container-fluid">
    <!-- Bouton retour -->
    <div class="mb-4">
        <a href="{{ route('technician.dashboard') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Retour au tableau de bord
        </a>
    </div>

    <!-- En-tête de la page -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body" style="background: linear-gradient(135deg, var(--main-color) 0%, var(--main-color-dark) 100%); color: white; border-radius: 10px;">
            <h2 class="mb-1">
                <i class="fas fa-clipboard-list me-2"></i>
                Mes demandes assignées
            </h2>
            <p class="mb-0 opacity-75">Mes demandes</p>
        </div>
    </div>

    <!-- Tableau des demandes -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if($requests->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead style="background-color: var(--main-color); color: white;">
                            <tr>
                                <th>ID</th>
                                <th>Employé</th>
                                <th>Département</th>
                                <th>Équipements</th>
                                <th>Date limite</th>
                                <th>Statut</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Boucle sur les demandes avec ID séquentiel d'affichage --}}
                            @foreach($requests as $index => $request)
                                <tr>
                                    {{-- Afficher numéro séquentiel (#1, #2, #3...) --}}
                                    <td><strong>#{{ $index + 1 }}</strong></td>

                                    <td>
                                        <i class="fas fa-user me-2 text-muted"></i>
                                        <strong>{{ $request->employee_name }}</strong>
                                    </td>
                                    <td>
                                        <i class="fas fa-building me-2 text-muted"></i>
                                        {{ $request->department }}
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{-- Afficher les 50 premiers caractères de la description --}}
                                            {{ Str::limit($request->equipment_description, 50) }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($request->deadline)
                                            <i class="fas fa-clock me-2 text-muted"></i>
                                            {{ $request->deadline->format('d/m/Y') }}
                                        @else
                                            <span class="text-muted">Non définie</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($request->status === 'en_cours')
                                            <span class="badge bg-info">
                                                🔵 En cours
                                            </span>
                                        @else
                                            <span class="badge bg-success">
                                                ✅ Terminé
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        {{-- Utilise l'ID réel de la base de données pour le routage --}}
                                        <a href="{{ route('technician.details', $request->id) }}" class="btn btn-sm" style="background: var(--main-color); color: white;">
                                            <i class="fas fa-eye me-1"></i> Détails
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <!-- État vide (aucune demande) -->
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">Aucune demande assignée</h4>
                    <p class="text-muted">Vous n'avez pas encore de demandes à traiter</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
