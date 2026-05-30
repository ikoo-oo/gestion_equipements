@extends('layouts.app')

@section('title', 'Modifier une demande')

@section('content')
<div class="container" style="max-width: 800px;">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('hr.requests') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Retour aux demandes
        </a>
    </div>

    <!-- Page Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body text-center py-4" style="background: linear-gradient(135deg, var(--main-color) 0%, var(--main-color-dark) 100%); color: white; border-radius: 10px;">
            <h2 class="mb-2">
                <i class="fas fa-edit me-2"></i>
                Modifier la demande
            </h2>
            <p class="mb-0 opacity-75">Modifiez les informations de l'employé</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('hr.update', $request->id) }}">
                @csrf
                @method('PUT')

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
                        value="{{ old('employee_name', $request->employee_name) }}"
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
                        <option value="IT" {{ old('department', $request->department) == 'IT' ? 'selected' : '' }}>IT (Informatique)</option>
                        <option value="RH" {{ old('department', $request->department) == 'RH' ? 'selected' : '' }}>RH (Ressources Humaines)</option>
                        <option value="Finance" {{ old('department', $request->department) == 'Finance' ? 'selected' : '' }}>Finance</option>
                        <option value="Commercial" {{ old('department', $request->department) == 'Commercial' ? 'selected' : '' }}>Commercial</option>
                        <option value="Marketing" {{ old('department', $request->department) == 'Marketing' ? 'selected' : '' }}>Marketing</option>
                        <option value="Production" {{ old('department', $request->department) == 'Production' ? 'selected' : '' }}>Production</option>
                        <option value="Logistique" {{ old('department', $request->department) == 'Logistique' ? 'selected' : '' }}>Logistique</option>
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
                        <option value="employe" {{ old('position', $request->position) == 'employe' ? 'selected' : '' }}>Employé</option>
                        <option value="apprenti" {{ old('position', $request->position) == 'apprenti' ? 'selected' : '' }}>Apprenti</option>
                        <option value="stagiaire" {{ old('position', $request->position) == 'stagiaire' ? 'selected' : '' }}>Stagiaire</option>
                    </select>
                    @error('position')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-lg" style="background: linear-gradient(135deg, var(--main-color) 0%, var(--main-color-dark) 100%); color: white; border: none;">
                        <i class="fas fa-save me-2"></i>
                        Enregistrer les modifications
                    </button>
                    <a href="{{ route('hr.requests') }}" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-times me-2"></i>
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>

  
</div>
@endsection
