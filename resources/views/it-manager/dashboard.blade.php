@extends('layouts.app')

@section('title', 'Tableau de bord IT Manager')

@section('content')
<div class="dashboard-page">
    <!-- Header -->
    <div class="dashboard-header">
        <h2>
            <i class="fas fa-cogs me-2"></i>
            Bienvenue, {{ auth()->user()->name }}!
        </h2>
        <p class="mb-0">Gestion et assignation des demandes</p>
    </div>

    <!-- Dashboard Cards - Custom Control -->
    <div class="dashboard-cards-wrapper">
        <a href="{{ route('it-manager.received') }}" class="dashboard-card-custom">
            <div class="dashboard-card-icon-custom">
                <i class="fas fa-inbox"></i>
            </div>
            <h4>Demandes reçues</h4>
            <p>Consulter les demandes</p>
        </a>

        <a href="{{ route('it-manager.assigned') }}" class="dashboard-card-custom">
            <div class="dashboard-card-icon-custom">
                <i class="fas fa-tasks"></i>
            </div>
            <h4>Demandes assignées</h4>
            <p>Voir les demandes assignées aux techniciens</p>
        </a>
    </div>

    <!-- Quick Stats - Custom Control -->
    <div class="stats-cards-wrapper">
        <div class="stat-card-custom">
            <h3 class="text-warning">{{ $newRequests ?? 0 }}</h3>
            <small>Nouvelles demandes</small>
        </div>
        <div class="stat-card-custom">
            <h3 class="text-info">{{ $inProgress ?? 0 }}</h3>
            <small>En cours de traitement</small>
        </div>
        <div class="stat-card-custom">
            <h3 class="text-success">{{ $completedThisMonth ?? 0 }}</h3>
            <small>Terminées ce mois</small>
        </div>
    </div>
</div>
@endsection
