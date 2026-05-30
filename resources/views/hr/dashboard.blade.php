@extends('layouts.app')

@section('title', 'Tableau de bord RH')

@section('content')
<div class="dashboard-page">
    <!-- Header -->
    <div class="dashboard-header">
        <h2>
            <i class="fas fa-home me-2"></i>
            Bienvenue, {{ auth()->user()->name }}!
        </h2>
        <p class="mb-0">Tableau de bord RH - Gestion des demandes d'équipements</p>
    </div>

    <!-- Dashboard Cards - Custom Control -->
    <div class="dashboard-cards-wrapper">
        <a href="{{ route('hr.create') }}" class="dashboard-card-custom">
            <div class="dashboard-card-icon-custom">
                <i class="fas fa-plus"></i>
            </div>
            <h4>Créer une demande</h4>
            <p>Ajouter une nouvelle demande d'équipement</p>
        </a>

        <a href="{{ route('hr.requests') }}" class="dashboard-card-custom">
            <div class="dashboard-card-icon-custom">
                <i class="fas fa-list-alt"></i>
            </div>
            <h4>Voir les demandes</h4>
            <p>Consulter toutes les demandes créées</p>
        </a>
    </div>

    <!-- Quick Stats - Custom Control -->
    <div class="stats-cards-wrapper">
        <div class="stat-card-custom">
            <h3 style="color: var(--main-color);">{{ $totalRequests ?? 0 }}</h3>
            <small>Total demandes</small>
        </div>
        <div class="stat-card-custom">
            <h3 class="text-warning">{{ $pending ?? 0 }}</h3>
            <small>En attente</small>
        </div>
        <div class="stat-card-custom">
            <h3 class="text-info">{{ $inProgress ?? 0 }}</h3>
            <small>En cours</small>
        </div>
        <div class="stat-card-custom">
            <h3 class="text-success">{{ $completed ?? 0 }}</h3>
            <small>Terminées</small>
        </div>
    </div>
</div>
@endsection
