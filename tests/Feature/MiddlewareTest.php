<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MiddlewareTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────────────────
    // HELPER
    // ──────────────────────────────────────────────────────────
    private function makeUser(string $role, string $email = null): User
    {
        return User::create([
            'name'     => ucfirst($role),
            'email'    => $email ?? "{$role}@test.com",
            'password' => 'secret',
            'role'     => $role,
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // FIX FOR ALL MIDDLEWARE TESTS:
    //
    // WHY we use assertStatus(302) + assertSessionHasErrors('access'):
    //
    // Your middleware does:
    //   return redirect()->route('login')->withErrors(['access' => 'Accès non autorisé...']);
    //
    // This means:
    //   - HTTP status = 302 (redirect), NOT 403 (forbidden)
    //   - Session contains errors with key 'access'
    //   - Redirects to route('login') = '/'
    //
    // Testing BOTH the 302 AND the 'access' error key is more
    // thorough than just testing the status code alone.
    // ──────────────────────────────────────────────────────────

    // ── Cross-role access tests (should be BLOCKED) ──────────

    public function test_hr_cannot_access_it_manager_dashboard(): void
    {
        $hr = $this->makeUser('hr');

        $response = $this->actingAs($hr)->get('/it-manager/dashboard');

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHasErrors('access');
    }

    public function test_hr_cannot_access_technician_dashboard(): void
    {
        $hr = $this->makeUser('hr');

        $response = $this->actingAs($hr)->get('/technician/dashboard');

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHasErrors('access');
    }

    public function test_it_manager_cannot_access_hr_dashboard(): void
    {
        $it = $this->makeUser('it_manager');

        $response = $this->actingAs($it)->get('/hr/dashboard');

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHasErrors('access');
    }

    public function test_it_manager_cannot_access_technician_dashboard(): void
    {
        $it = $this->makeUser('it_manager');

        $response = $this->actingAs($it)->get('/technician/dashboard');

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHasErrors('access');
    }

    public function test_technician_cannot_access_hr_dashboard(): void
    {
        $tech = $this->makeUser('technician');

        $response = $this->actingAs($tech)->get('/hr/dashboard');

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHasErrors('access');
    }

    public function test_technician_cannot_access_it_manager_dashboard(): void
    {
        $tech = $this->makeUser('technician');

        $response = $this->actingAs($tech)->get('/it-manager/dashboard');

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHasErrors('access');
    }

    // ── Correct-role access tests (should be ALLOWED) ────────

    public function test_hr_can_access_own_dashboard(): void
    {
        $hr = $this->makeUser('hr');

        $response = $this->actingAs($hr)->get('/hr/dashboard');

        $response->assertStatus(200);
    }

    public function test_it_manager_can_access_own_dashboard(): void
    {
        $it = $this->makeUser('it_manager');

        $response = $this->actingAs($it)->get('/it-manager/dashboard');

        $response->assertStatus(200);
    }

    public function test_technician_can_access_own_dashboard(): void
    {
        $tech = $this->makeUser('technician');

        $response = $this->actingAs($tech)->get('/technician/dashboard');

        $response->assertStatus(200);
    }

    // ── Guest (unauthenticated) access ───────────────────────

    public function test_guest_is_redirected_from_hr_routes(): void
    {
        // No actingAs = unauthenticated guest
        // CheckHR: Auth::check() is false → redirect to route('login') = '/'
        $this->get('/hr/dashboard')->assertRedirect('/');
        $this->get('/hr/requests')->assertRedirect('/');
        $this->get('/hr/create')->assertRedirect('/');
    }

    public function test_guest_is_redirected_from_it_manager_routes(): void
    {
        $this->get('/it-manager/dashboard')->assertRedirect('/');
        $this->get('/it-manager/received')->assertRedirect('/');
    }

    public function test_guest_is_redirected_from_technician_routes(): void
    {
        $this->get('/technician/dashboard')->assertRedirect('/');
        $this->get('/technician/requests')->assertRedirect('/');
    }
}