@extends('layouts.app')

@section('title', 'Demandes assignées')

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
                <i class="fas fa-tasks me-2"></i>
                Demandes assignées aux techniciens
            </h2>
            <p class="mb-0 opacity-75">Suivi des demandes </p>
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
                                <th>Équipements</th>
                                <th>Assigné à</th>
                                <th>Date limite</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Loop through requests with sequential display ID --}}
                            @foreach($requests as $index => $request)
                                <tr>
                                    {{-- Display sequential number (#1, #2, #3...) --}}
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
                                            {{ Str::limit($request->equipment_description, 50) }}
                                        </small>
                                    </td>
                                    <td>
                                        <i class="fas fa-user-cog me-2 text-muted"></i>
                                        {{ $request->technician->name ?? 'N/A' }}
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
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">Aucune demande assignée</h4>
                    <p class="text-muted">Les demandes assignées apparaîtront ici</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
