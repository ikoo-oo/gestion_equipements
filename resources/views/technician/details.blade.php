@extends('layouts.app')

@section('title', 'Détails de la demande')

@section('content')
<div class="container" style="max-width: 900px;">
    <!-- Bouton retour -->
    <div class="mb-4">
        <a href="{{ route('technician.requests') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Retour à mes demandes
        </a>
    </div>

    <!-- En-tête de la page -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body text-center py-4" style="background: linear-gradient(135deg, var(--main-color) 0%, var(--main-color-dark) 100%); color: white; border-radius: 10px;">
            <h2 class="mb-2">
                <i class="fas fa-file-alt me-2"></i>
                Détails de la demande
            </h2>
            <p class="mb-0 opacity-75">Informations complètes de la demande</p>
        </div>
    </div>

    <!-- Carte des détails -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <!-- Section 1: Informations de l'employé -->
            <div class="mb-4 p-3" style="background: #f8f9fa; border-left: 4px solid var(--main-color); border-radius: 8px;">
                <h5 class="mb-3">
                    <i class="fas fa-user me-2" style="color: var(--main-color);"></i>
                    Informations de l'employé
                </h5>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold text-muted">Nom complet</label>
                        <div class="p-2 bg-white rounded">
                            <i class="fas fa-user me-2 text-muted"></i>
                            <strong>{{ $request->employee_name }}</strong>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold text-muted">Département</label>
                        <div class="p-2 bg-white rounded">
                            <i class="fas fa-building me-2 text-muted"></i>
                            {{ $request->department }}
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold text-muted">Poste</label>
                        <div class="p-2 bg-white rounded">
                            <span class="badge bg-secondary">{{ ucfirst($request->position) }}</span>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold text-muted">Demande créée par</label>
                        <div class="p-2 bg-white rounded">
                            <i class="fas fa-user-tie me-2 text-muted"></i>
                            {{ $request->creator->name ?? 'N/A' }} (RH)
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Détails techniques -->
            <div class="mb-4 p-3" style="background: #f8f9fa; border-left: 4px solid var(--main-color); border-radius: 8px;">
                <h5 class="mb-3">
                    <i class="fas fa-laptop me-2" style="color: var(--main-color);"></i>
                    Équipements à fournir
                </h5>

                <div class="p-3 bg-white rounded">
                    <p class="mb-0" style="white-space: pre-line;">{{ $request->equipment_description }}</p>
                </div>
            </div>

            <!-- Section 3: Informations de suivi -->
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-calendar fa-2x mb-2" style="color: var(--main-color);"></i>
                            <h6 class="text-muted mb-1">Date de création</h6>
                            <strong>{{ $request->created_at->format('d/m/Y H:i') }}</strong>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-clock fa-2x mb-2" style="color: var(--main-color);"></i>
                            <h6 class="text-muted mb-1">Date limite</h6>
                            <strong>
                                @if($request->deadline)
                                    {{ $request->deadline->format('d/m/Y') }}
                                @else
                                    Non définie
                                @endif
                            </strong>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-info-circle fa-2x mb-2" style="color: var(--main-color);"></i>
                            <h6 class="text-muted mb-1">Statut actuel</h6>
                            @if($request->status === 'en_cours')
                                <span class="badge bg-info">🔵 En cours</span>
                            @else
                                <span class="badge bg-success">✅ Terminé</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bouton d'action -->
    @if($request->status === 'en_cours')
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="mb-3">
                    <i class="fas fa-check-circle me-2" style="color: var(--main-color);"></i>
                    Marquer comme terminé
                </h5>

                <p class="text-muted mb-4">
                    Une fois que vous avez livré tous les équipements à l'employé, cliquez sur le bouton ci-dessous pour marquer cette demande comme terminée.
                </p>

                <form action="{{ route('technician.complete', $request->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr d\'avoir livré tous les équipements? Cette action marquera la demande comme terminée.')">
                    @csrf
                    <button type="submit" class="btn btn-success btn-lg w-100">
                        <i class="fas fa-check-double me-2"></i>
                        Marquer comme terminé
                    </button>
                </form>
            </div>
        </div>
    @else
        <div class="alert alert-success border-0 shadow-sm">
            <i class="fas fa-check-circle me-2"></i>
            <strong>Demande terminée!</strong> Cette demande a été marquée comme terminée.
        </div>
    @endif
</div>
@endsection
