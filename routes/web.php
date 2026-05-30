<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

// ============================================
// PUBLIC ROUTES (No authentication required)
// ============================================

Route::get('/', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Forgot Password Routes (just for show)
Route::get('/forgot-password', [LoginController::class, 'showForgotPassword'])->name('forgot-password');
Route::post('/forgot-password', [LoginController::class, 'forgotPassword'])->name('forgot-password.post');

// ============================================
// HR ROUTES (Protected by auth + check.hr)
// ============================================


Route::middleware(['auth', 'check.hr'])->prefix('hr')->name('hr.')->group(function() {
    // Dashboard
    Route::get('/dashboard', [App\Http\Controllers\HRController::class, 'dashboard'])->name('dashboard');

    // Create new request
    Route::get('/create', [App\Http\Controllers\HRController::class, 'create'])->name('create');
    Route::post('/store', [App\Http\Controllers\HRController::class, 'store'])->name('store');

    // View all requests
    Route::get('/requests', [App\Http\Controllers\HRController::class, 'requests'])->name('requests');

    // Edit request (NEW!)
    Route::get('/edit/{id}', [App\Http\Controllers\HRController::class, 'edit'])->name('edit');
    Route::put('/update/{id}', [App\Http\Controllers\HRController::class, 'update'])->name('update');

    // Delete request
    Route::delete('/delete/{id}', [App\Http\Controllers\HRController::class, 'destroy'])->name('delete');

    // Notifications
    Route::get('/notifications', [App\Http\Controllers\HRController::class, 'notifications'])->name('notifications');



Route::middleware(['auth', 'check.hr'])->prefix('hr')->name('hr.')->group(function() {
    // ... existing routes ...

    // Clear notifications
    Route::post('/notifications/clear', [App\Http\Controllers\HRController::class, 'clearNotifications'])->name('notifications.clear');
});

    });

// ============================================
// IT MANAGER ROUTES (Protected by auth + check.it_manager)
// ============================================

Route::middleware(['auth', 'check.it_manager'])->prefix('it-manager')->name('it-manager.')->group(function() {
    // Dashboard
    Route::get('/dashboard', [App\Http\Controllers\ITManagerController::class, 'dashboard'])->name('dashboard');

    // View received requests (status = "en_attente")
    Route::get('/received', [App\Http\Controllers\ITManagerController::class, 'received'])->name('received');

    // Assign request to technician
    Route::get('/assign/{id}', [App\Http\Controllers\ITManagerController::class, 'showAssign'])->name('assign.show');
    Route::post('/assign/{id}', [App\Http\Controllers\ITManagerController::class, 'assign'])->name('assign');

    // View assigned requests (already assigned)
    Route::get('/assigned', [App\Http\Controllers\ITManagerController::class, 'assigned'])->name('assigned');

    // Notifications
    Route::get('/notifications', [App\Http\Controllers\ITManagerController::class, 'notifications'])->name('notifications');
});

// ============================================
// TECHNICIAN ROUTES (Protected by auth + check.technician)
// ============================================

Route::middleware(['auth', 'check.technician'])->prefix('technician')->name('technician.')->group(function() {
    // Tableau de bord
    Route::get('/dashboard', [App\Http\Controllers\TechnicianController::class, 'dashboard'])->name('dashboard');

    // Voir mes demandes assignées
    Route::get('/requests', [App\Http\Controllers\TechnicianController::class, 'requests'])->name('requests');

    // Voir les détails d'une demande
    Route::get('/details/{id}', [App\Http\Controllers\TechnicianController::class, 'details'])->name('details');

    // Marquer une demande comme terminée
    Route::post('/complete/{id}', [App\Http\Controllers\TechnicianController::class, 'complete'])->name('complete');

    // Notifications
    Route::get('/notifications', [App\Http\Controllers\TechnicianController::class, 'notifications'])->name('notifications');
});
