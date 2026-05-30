@extends('layouts.app')

@section('title', 'Tableau de bord Technicien')

@section('content')
<div class="dashboard-page">
    <!-- En-tête -->
    <div class="dashboard-header">
        <h2>
            <i class="fas fa-tools me-2"></i>
            Bienvenue, {{ auth()->user()->name }}!
        </h2>
        <p class="mb-0">Tableau de bord Technicien - Mes demandes assignées</p>
    </div>

    <!-- Dashboard Card - Custom Control (Single Card) -->
    <div class="dashboard-cards-wrapper">
        <a href="{{ route('technician.requests') }}" class="dashboard-card-custom">
            <div class="dashboard-card-icon-custom">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <h4>Mes demandes assignées</h4>
            <p>Consulter et traiter mes demandes</p>
        </a>
    </div>

    <!-- Quick Stats - Custom Control -->
    <div class="stats-cards-wrapper">
        <div class="stat-card-custom">
            <h3 class="text-info">{{ $inProgress ?? 0 }}</h3>
            <small>En cours</small>
        </div>
        <div class="stat-card-custom">
            <h3 class="text-success">{{ $completedThisMonth ?? 0 }}</h3>
            <small>Terminées ce mois</small>
        </div>
        <div class="stat-card-custom">
            <h3 style="color: var(--main-color);">{{ $totalAssigned ?? 0 }}</h3>
            <small>Total assignées</small>
        </div>
    </div>
</div>
@endsection
