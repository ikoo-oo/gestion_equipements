<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\EquipmentRequest;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ITManagerTest extends TestCase
{
    use RefreshDatabase;

    private User $hr;
    private User $itManager;
    private User $technician;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hr = User::create([
            'name' => 'HR', 'email' => 'hr@test.com',
            'password' => 'secret', 'role' => 'hr',
        ]);
        $this->itManager = User::create([
            'name' => 'IT Mgr', 'email' => 'it@test.com',
            'password' => 'secret', 'role' => 'it_manager',
        ]);
        $this->technician = User::create([
            'name' => 'Tech', 'email' => 'tech@test.com',
            'password' => 'secret', 'role' => 'technician',
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // HELPER
    // ──────────────────────────────────────────────────────────
    private function makeRequest(array $overrides = []): EquipmentRequest
    {
        return EquipmentRequest::create(array_merge([
            'employee_name' => 'Test Employee',
            'department'    => 'IT',
            'position'      => 'employe',
            'status'        => 'en_attente',
            'created_by'    => $this->hr->id,
        ], $overrides));
    }

    // ──────────────────────────────────────────────────────────
    // TEST 1: IT Manager can view their dashboard
    // ──────────────────────────────────────────────────────────
    public function test_it_manager_can_view_dashboard(): void
    {
        $response = $this->actingAs($this->itManager)->get('/it-manager/dashboard');

        $response->assertStatus(200);
    }

    // ──────────────────────────────────────────────────────────
    // TEST 2: IT Manager sees pending requests in received page
    // WHY: received() queries status = 'en_attente'
    // ──────────────────────────────────────────────────────────
    public function test_it_manager_sees_pending_requests_in_received(): void
    {
        $this->makeRequest(['employee_name' => 'Nouvel Employé']);

        $response = $this->actingAs($this->itManager)->get('/it-manager/received');

        $response->assertStatus(200);
        $response->assertSee('Nouvel Employé');
    }

    // ──────────────────────────────────────────────────────────
    // TEST 3: IT Manager does NOT see in-progress requests in received
    // WHY: received() filters ONLY 'en_attente'
    // ──────────────────────────────────────────────────────────
    public function test_it_manager_does_not_see_assigned_requests_in_received(): void
    {
        $this->makeRequest([
            'employee_name' => 'Already Assigned',
            'status'        => 'en_cours',
            'assigned_to'   => $this->technician->id,
        ]);

        $response = $this->actingAs($this->itManager)->get('/it-manager/received');

        $response->assertDontSee('Already Assigned');
    }

    // ──────────────────────────────────────────────────────────
    // TEST 4: IT Manager can view the assign form
    // ──────────────────────────────────────────────────────────
    public function test_it_manager_can_view_assign_form(): void
    {
        $request = $this->makeRequest();

        $response = $this->actingAs($this->itManager)
            ->get("/it-manager/assign/{$request->id}");

        $response->assertStatus(200);
    }

    // ──────────────────────────────────────────────────────────
    // TEST 5: IT Manager can assign a request to a technician
    // KEY FACTS from ITManagerController::assign():
    //   - Form field is 'assigned_to' (NOT 'technician_id')
    //   - Form field is 'equipment_description' (NOT 'equipment_desc')
    //   - Status changes to 'en_cours'
    //   - Redirects to route('it-manager.dashboard')
    // ──────────────────────────────────────────────────────────
    public function test_it_manager_can_assign_request_to_technician(): void
    {
        $request = $this->makeRequest(['employee_name' => 'Karim Boudiaf']);

        $response = $this->actingAs($this->itManager)
            ->post("/it-manager/assign/{$request->id}", [
                'equipment_description' => 'PC Dell + Écran 24" + Clavier',
                'deadline'              => '2026-06-15',
                'assigned_to'           => $this->technician->id,
            ]);

        $response->assertRedirect('/it-manager/dashboard');

        // Status must be 'en_cours' now
        $this->assertDatabaseHas('equipment_requests', [
            'id'          => $request->id,
            'status'      => 'en_cours',
            'assigned_to' => $this->technician->id,
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // TEST 6: Assigning a request saves the equipment description
    // ──────────────────────────────────────────────────────────
    public function test_it_manager_assign_saves_equipment_description(): void
    {
        $request = $this->makeRequest();

        $this->actingAs($this->itManager)
            ->post("/it-manager/assign/{$request->id}", [
                'equipment_description' => 'Laptop HP EliteBook 840',
                'deadline'              => null,
                'assigned_to'           => $this->technician->id,
            ]);

        $this->assertDatabaseHas('equipment_requests', [
            'id'                    => $request->id,
            'equipment_description' => 'Laptop HP EliteBook 840',
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // TEST 7: Assigning creates a notification for the technician
    // WHY: assign() creates Notification with type='assigned_request'
    //      for the assigned technician
    // ──────────────────────────────────────────────────────────
    public function test_assigning_request_notifies_technician(): void
    {
        $request = $this->makeRequest();

        $this->actingAs($this->itManager)
            ->post("/it-manager/assign/{$request->id}", [
                'equipment_description' => 'Laptop HP',
                'deadline'              => null,
                'assigned_to'           => $this->technician->id,
            ]);

        $this->assertDatabaseHas('notifications', [
            'user_id'    => $this->technician->id,
            'type'       => 'assigned_request',
            'request_id' => $request->id,
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // TEST 8: Assigning DELETES the 'new_request' notification
    // WHY: assign() calls Notification::where(...)->delete()
    //      to clean up the IT Manager's 'new_request' notification
    // ──────────────────────────────────────────────────────────
    public function test_assigning_deletes_new_request_notification(): void
    {
        $request = $this->makeRequest();

        // Simulate the notification that was created when HR made the request
        Notification::create([
            'user_id'    => $this->itManager->id,
            'type'       => 'new_request',
            'message'    => 'Nouvelle demande',
            'request_id' => $request->id,
        ]);

        $this->actingAs($this->itManager)
            ->post("/it-manager/assign/{$request->id}", [
                'equipment_description' => 'Monitor + PC',
                'deadline'              => null,
                'assigned_to'           => $this->technician->id,
            ]);

        // 'new_request' notification must be gone
        $this->assertDatabaseMissing('notifications', [
            'request_id' => $request->id,
            'type'       => 'new_request',
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // TEST 9: Assign validation fails without required fields
    // ──────────────────────────────────────────────────────────
    public function test_assign_validation_fails_without_required_fields(): void
    {
        $request = $this->makeRequest();

        $response = $this->actingAs($this->itManager)
            ->post("/it-manager/assign/{$request->id}", []);

        $response->assertSessionHasErrors(['equipment_description', 'assigned_to']);
    }

    // ──────────────────────────────────────────────────────────
    // TEST 10: IT Manager can view assigned requests list
    // ──────────────────────────────────────────────────────────
    public function test_it_manager_can_view_assigned_requests(): void
    {
        $this->makeRequest([
            'status'      => 'en_cours',
            'assigned_to' => $this->technician->id,
        ]);

        $response = $this->actingAs($this->itManager)->get('/it-manager/assigned');

        $response->assertStatus(200);
    }
}