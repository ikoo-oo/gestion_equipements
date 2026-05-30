@extends('layouts.app')

@section('title', 'Créer une demande')

@section('content')
<div class="container" style="max-width: 800px;">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('hr.dashboard') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Retour au tableau de bord
        </a>
    </div>

    <!-- Page Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body text-center py-4" style="background: linear-gradient(135deg, var(--main-color) 0%, var(--main-color-dark) 100%); color: white; border-radius: 10px;">
            <h2 class="mb-2">
                <i class="fas fa-plus-circle me-2"></i>
                Créer une demande d'équipement
            </h2>
            <p class="mb-0 opacity-75">Remplissez les informations de l'employé</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('hr.store') }}">
                @csrf

                <!-- Employee Name -->
                <div class="mb-4">
                    <label for="employee_name" class="form-label fw-semibold">
                        <i class="fas fa-user me-1" style="color: var(--main-color);"></i>
                        Nom de l'employé <span class="text-danger">*</span>
                    </label>
                    <input
                        type="text"
                        class="form-control form-control-lg @error('employee_name') is-invalid @enderror"
                        id="employee_name"
                        name="employee_name"
                        value="{{ old('employee_name') }}"
                        placeholder="Ex: Jean Dupont"
                        required
                        autofocus
                    >
                    @error('employee_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Department -->
                <div class="mb-4">
                    <label for="department" class="form-label fw-semibold">
                        <i class="fas fa-building me-1" style="color: var(--main-color);"></i>
                        Département <span class="text-danger">*</span>
                    </label>
                    <select
                        class="form-select form-select-lg @error('department') is-invalid @enderror"
                        id="department"
                        name="department"
                        required
                    >
                        <option value="">Sélectionner un département</option>
                        <option value="IT" {{ old('department') == 'IT' ? 'selected' : '' }}>IT (Informatique)</option>
                        <option value="RH" {{ old('department') == 'RH' ? 'selected' : '' }}>RH (Ressources Humaines)</option>
                        <option value="Finance" {{ old('department') == 'Finance' ? 'selected' : '' }}>Finance</option>
                        <option value="Commercial" {{ old('department') == 'Commercial' ? 'selected' : '' }}>Commercial</option>
                        <option value="Marketing" {{ old('department') == 'Marketing' ? 'selected' : '' }}>Marketing</option>
                        <option value="Production" {{ old('department') == 'Production' ? 'selected' : '' }}>Production</option>
                        <option value="Logistique" {{ old('department') == 'Logistique' ? 'selected' : '' }}>Logistique</option>
                    </select>
                    @error('department')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Position -->
                <div class="mb-4">
                    <label for="position" class="form-label fw-semibold">
                        <i class="fas fa-briefcase me-1" style="color: var(--main-color);"></i>
                        Poste <span class="text-danger">*</span>
                    </label>
                    <select
                        class="form-select form-select-lg @error('position') is-invalid @enderror"
                        id="position"
                        name="position"
                        required
                    >
                        <option value="">Sélectionner un poste</option>
                        <option value="employe" {{ old('position') == 'employe' ? 'selected' : '' }}>Employé</option>
                        <option value="apprenti" {{ old('position') == 'apprenti' ? 'selected' : '' }}>Apprenti</option>
                        <option value="stagiaire" {{ old('position') == 'stagiaire' ? 'selected' : '' }}>Stagiaire</option>
                    </select>
                    @error('position')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <br><br>

                <!-- Submit Button -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-lg" style="background: linear-gradient(135deg, var(--main-color) 0%, var(--main-color-dark) 100%); color: white; border: none;">
                        <i class="fas fa-check-circle me-2"></i>
                        Créer la demande
                    </button>
                    <a href="{{ route('hr.dashboard') }}" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-times me-2"></i>
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>

   <br>
</div>
@endsection
