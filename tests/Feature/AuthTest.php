<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────────────────
    // HELPERS
    // WHY helpers: DRY principle — one place to update if schema changes
    // NOTE: No Hash::make() needed — User model has 'password' => 'hashed' cast
    //       Laravel auto-hashes plain strings on save
    // ──────────────────────────────────────────────────────────

    private function createHR(): User
    {
        return User::create([
            'name'     => 'HR User',
            'email'    => 'hr@test.com',
            'password' => 'password123',
            'role'     => 'hr',
        ]);
    }

    private function createITManager(): User
    {
        return User::create([
            'name'     => 'IT Manager',
            'email'    => 'it@test.com',
            'password' => 'password123',
            'role'     => 'it_manager',
        ]);
    }

    private function createTechnician(): User
    {
        return User::create([
            'name'     => 'Technician',
            'email'    => 'tech@test.com',
            'password' => 'password123',
            'role'     => 'technician',
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // TEST 1: Login page loads
    // FIX: Route GET '/' is your login page (name='login'),
    //      not GET '/login' which doesn't exist → was giving 405
    // ──────────────────────────────────────────────────────────
    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    // ──────────────────────────────────────────────────────────
    // TEST 2: HR login redirects to HR dashboard
    // ──────────────────────────────────────────────────────────
    public function test_hr_can_login_and_redirect_to_hr_dashboard(): void
    {
        $this->createHR();

        $response = $this->post('/login', [
            'email'    => 'hr@test.com',
            'password' => 'password123',
        ]);

        // redirectToDashboard() → redirect()->route('hr.dashboard') = /hr/dashboard
        $response->assertRedirect('/hr/dashboard');
    }

    // ──────────────────────────────────────────────────────────
    // TEST 3: IT Manager login
    // ──────────────────────────────────────────────────────────
    public function test_it_manager_can_login_and_redirect_to_it_dashboard(): void
    {
        $this->createITManager();

        $response = $this->post('/login', [
            'email'    => 'it@test.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/it-manager/dashboard');
    }

    // ──────────────────────────────────────────────────────────
    // TEST 4: Technician login
    // ──────────────────────────────────────────────────────────
    public function test_technician_can_login_and_redirect_to_technician_dashboard(): void
    {
        $this->createTechnician();

        $response = $this->post('/login', [
            'email'    => 'tech@test.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/technician/dashboard');
    }

    // ──────────────────────────────────────────────────────────
    // TEST 5: Wrong password → login fails
    // WHY: LoginController returns back()->withErrors(['email' => '...'])
    // ──────────────────────────────────────────────────────────
    public function test_login_fails_with_wrong_password(): void
    {
        $this->createHR();

        $response = $this->post('/login', [
            'email'    => 'hr@test.com',
            'password' => 'WRONG_PASSWORD',
        ]);

        $response->assertSessionHasErrors('email');
    }

    // ──────────────────────────────────────────────────────────
    // TEST 6: Nonexistent email → login fails
    // ──────────────────────────────────────────────────────────
    public function test_login_fails_with_nonexistent_email(): void
    {
        $response = $this->post('/login', [
            'email'    => 'ghost@nobody.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    // ──────────────────────────────────────────────────────────
    // TEST 7: Logout redirects to '/'
    // FIX: LoginController::logout() does redirect()->route('login')
    //      route('login') = GET '/' → so redirect target is '/'
    // ──────────────────────────────────────────────────────────
    public function test_authenticated_user_can_logout(): void
    {
        $hr = $this->createHR();

        $response = $this->actingAs($hr)->post('/logout');

        // route('login') resolves to '/' not '/login'
        $response->assertRedirect('/');
    }

    // ──────────────────────────────────────────────────────────
    // TEST 8: Guest redirected away from protected route
    // FIX: Middleware redirects to route('login') = '/' not '/login'
    // ──────────────────────────────────────────────────────────
    public function test_guest_cannot_access_hr_dashboard(): void
    {
        $response = $this->get('/hr/dashboard');

        $response->assertRedirect('/');
    }

    // ──────────────────────────────────────────────────────────
    // TEST 9: Already logged in user going to '/' is redirected
    // WHY: showLogin() checks Auth::check() and calls redirectToDashboard()
    // ──────────────────────────────────────────────────────────
    public function test_already_logged_in_hr_is_redirected_from_login_page(): void
    {
        $hr = $this->createHR();

        $response = $this->actingAs($hr)->get('/');

        $response->assertRedirect('/hr/dashboard');
    }

    // ──────────────────────────────────────────────────────────
    // TEST 10: Login validation — empty fields
    // ──────────────────────────────────────────────────────────
    public function test_login_requires_email_and_password(): void
    {
        $response = $this->post('/login', []);

        $response->assertSessionHasErrors(['email', 'password']);
    }
}