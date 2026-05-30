@extends('layouts.app')

@section('title', 'Demandes reçues')

@section('content')
<div class="container-fluid">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('it-manager.dashboard') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Retour au tableau de bord
        </a>
    </div>

    <!-- Page Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body" style="background: linear-gradient(135deg, var(--main-color) 0%, var(--main-color-dark) 100%); color: white; border-radius: 10px;">
            <h2 class="mb-1">
                <i class="fas fa-inbox me-2"></i>
                Demandes reçues du département RH
            </h2>
            <p class="mb-0 opacity-75">Demandes en attente d'assignation</p>
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
                                <th>Créé par (RH)</th>
                                <th>Date de création</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                     <tbody>
    {{-- Loop through requests with sequential display ID --}}
    @foreach($requests as $index => $request)
        <tr>
            {{-- Display sequential number (#1, #2, #3...) instead of database ID --}}
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
                <span class="badge bg-secondary">
                    {{ ucfirst($request->position) }}
                </span>
            </td>
            <td>
                <i class="fas fa-user-tie me-2 text-muted"></i>
                {{ $request->creator->name ?? 'N/A' }}
            </td>
            <td>
                <i class="fas fa-calendar me-2 text-muted"></i>
                {{ $request->created_at->format('d/m/Y H:i') }}
            </td>
            <td>
                {{-- Uses real database ID for routing (not display ID) --}}
                <a href="{{ route('it-manager.assign.show', $request->id) }}" class="btn btn-sm" style="background: var(--main-color); color: white;">
                    <i class="fas fa-user-plus me-1"></i> Assigner
                </a>
            </td>
        </tr>
    @endforeach
</tbody>
                    </table>
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                    <h4 class="text-muted">Aucune demande en attente</h4>
                    <p class="text-muted">Toutes les demandes ont été assignées!</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
