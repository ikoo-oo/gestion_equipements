@extends('layouts.app')

@section('title', 'Assigner une demande')

@section('content')
<div class="container" style="max-width: 900px;">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('it-manager.received') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Retour aux demandes reçues
        </a>
    </div>

    <!-- Page Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body text-center py-4" style="background: linear-gradient(135deg, var(--main-color) 0%, var(--main-color-dark) 100%); color: white; border-radius: 10px;">
            <h2 class="mb-2">
                <i class="fas fa-user-plus me-2"></i>
                Assigner la demande au technicien
            </h2>
            <p class="mb-0 opacity-75">Complétez les détails techniques et assignez à un technicien</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('it-manager.assign', $request->id) }}">
                @csrf

                <!-- Section 1: HR Data (Read-Only - Pre-filled) -->
                <div class="mb-4 p-3" style="background: #f8f9fa; border-left: 4px solid var(--main-color); border-radius: 8px;">
                    <h5 class="mb-3">
                        <i class="fas fa-info-circle me-2" style="color: var(--main-color);"></i>
                        Informations de la demande RH
                    </h5>

                    <div class="row">
                        <!-- Employee Name (Read-only) -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">
                                <i class="fas fa-user me-1"></i> Nom de l'employé
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                value="{{ $request->employee_name }}"
                                disabled
                                style="background-color: #e9ecef;"
                            >

                        </div>

                        <!-- Department (Read-only) -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">
                                <i class="fas fa-building me-1"></i> Département
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                value="{{ $request->department }}"
                                disabled
                                style="background-color: #e9ecef;"
                            >

                        </div>

                        <!-- Position (Read-only) -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">
                                <i class="fas fa-briefcase me-1"></i> Poste
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                value="{{ ucfirst($request->position) }}"
                                disabled
                                style="background-color: #e9ecef;"
                            >

                        </div>

                        <!-- Created Date (Read-only) -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">
                                <i class="fas fa-calendar me-1"></i> Date de création
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                value="{{ $request->created_at->format('d/m/Y H:i') }}"
                                disabled
                                style="background-color: #e9ecef;"
                            >

                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Section 2: IT Manager Input (Editable Fields) -->
                <div class="mb-4">
                    <h5 class="mb-3">
                        <i class="fas fa-tools me-2" style="color: var(--main-color);"></i>
                        Détails techniques à compléter
                    </h5>

                    <!-- Equipment Description (Required) -->
                    <div class="mb-4">
                        <label for="equipment_description" class="form-label fw-semibold">
                            <i class="fas fa-laptop me-1" style="color: var(--main-color);"></i>
                            Description des équipements <span class="text-danger">*</span>
                        </label>
                        <textarea
                            class="form-control form-control-lg @error('equipment_description') is-invalid @enderror"
                            id="equipment_description"
                            name="equipment_description"
                            rows="4"
                            placeholder="Ex: PC portable Dell XPS 15, Écran 24 pouces, Clavier sans fil, Souris optique, Casque audio..."
                            required
                        >{{ old('equipment_description') }}</textarea>
                        @error('equipment_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                       <br>
                    </div>

                    <!-- Deadline (Optional) -->
                    <div class="mb-4">
                        <label for="deadline" class="form-label fw-semibold">
                            <i class="fas fa-clock me-1" style="color: var(--main-color);"></i>
                            Date limite de livraison <span class="text-muted"></span>
                        </label>
                        <input
                            type="date"
                            class="form-control form-control-lg @error('deadline') is-invalid @enderror"
                            id="deadline"
                            name="deadline"
                            value="{{ old('deadline') }}"
                            min="{{ date('Y-m-d') }}"
                        >
                        @error('deadline')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                       <br>
                    </div>

                    <!-- Assign to Technician (Required) -->
                    <div class="mb-4">
                        <label for="assigned_to" class="form-label fw-semibold">
                            <i class="fas fa-user-cog me-1" style="color: var(--main-color);"></i>
                            Assigner au technicien <span class="text-danger">*</span>
                        </label>
                        <select
                            class="form-select form-select-lg @error('assigned_to') is-invalid @enderror"
                            id="assigned_to"
                            name="assigned_to"
                            required
                        >
                            <option value="">Sélectionner un technicien</option>
                            @foreach($technicians as $technician)
                                <option value="{{ $technician->id }}" {{ old('assigned_to') == $technician->id ? 'selected' : '' }}>
                                    {{ $technician->name }} ({{ $technician->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('assigned_to')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                       <br><br>
                       <br><br>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-lg" style="background: linear-gradient(135deg, var(--main-color) 0%, var(--main-color-dark) 100%); color: white; border: none;">
                        <i class="fas fa-check-circle me-2"></i>
                        Assigner au technicien
                    </button>
                    <a href="{{ route('it-manager.received') }}" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-times me-2"></i>
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>


</div>
@endsection
