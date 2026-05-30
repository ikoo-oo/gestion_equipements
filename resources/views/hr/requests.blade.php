@extends('layouts.app')

@section('title', 'Mes demandes')

@section('content')
<div class="container-fluid">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('hr.dashboard') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Retour au tableau de bord
        </a>
    </div>

    <!-- Page Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, var(--main-color) 0%, var(--main-color-dark) 100%); color: white; border-radius: 10px;">
            <div>
                <h2 class="mb-1">
                    <i class="fas fa-list-alt me-2"></i>
                    Mes demandes d'équipements
                </h2>
                <p class="mb-0 opacity-75">Toutes les demandes que vous avez créées</p>
            </div>
            <a href="{{ route('hr.create') }}" class="btn btn-light btn-lg">
                <i class="fas fa-plus me-2"></i> Nouvelle demande
            </a>
        </div>
    </div>

    <!-- Requests Table -->
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
                                <th>Poste</th>
                                <th>Date de création</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                       <tbody>
    @foreach($requests as $index => $request)
        <tr>
            {{-- Display sequential number instead of database ID --}}
            <td><strong>#{{ $index + 1 }}</strong></td>

            <td>
                <i class="fas fa-user me-2 text-muted"></i>
                {{ $request->employee_name }}
            </td>
            <td>
                <i class="fas fa-building me-2 text-muted"></i>
                {{ $request->department }}
            </td>
            <td>
                <span class="badge bg-secondary">
                    {{ ucfirst($request->position) }}
                </span>
            </td>
            <td>
                <i class="fas fa-calendar me-2 text-muted"></i>
                {{ $request->created_at->format('d/m/Y H:i') }}
            </td>
            <td>
                @if($request->status === 'en_attente')
                    <span class="badge bg-warning text-dark">
                        🟡 En attente
                    </span>
                @elseif($request->status === 'en_cours')
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
    @if($request->status === 'en_attente')
        {{-- Edit Button (only if status = en_attente) --}}
        <a href="{{ route('hr.edit', $request->id) }}" class="btn btn-sm btn-warning me-2">
            <i class="fas fa-edit"></i> Modifier
        </a>
    @endif

    {{-- Delete Button (if status = en_attente OR terminé) --}}
    @if($request->status === 'en_attente' || $request->status === 'termine')
        <form action="{{ route('hr.delete', $request->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette demande?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger">
                <i class="fas fa-trash"></i> Supprimer
            </button>
        </form>
    @endif
</td>

        </tr>
    @endforeach
</tbody>
                    </table>
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">Aucune demande pour le moment</h4>
                    <p class="text-muted mb-4">Créez votre première demande d'équipement</p>
                    <a href="{{ route('hr.create') }}" class="btn btn-lg" style="background: linear-gradient(135deg, var(--main-color) 0%, var(--main-color-dark) 100%); color: white;">
                        <i class="fas fa-plus me-2"></i> Créer une demande
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
