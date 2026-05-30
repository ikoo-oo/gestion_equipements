<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\EquipmentRequest;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HRTest extends TestCase
{
    use RefreshDatabase;

    private User $hr;
    private User $itManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hr = User::create([
            'name'     => 'HR User',
            'email'    => 'hr@test.com',
            'password' => 'secret',
            'role'     => 'hr',
        ]);

        // IT Manager needed because store() sends a notification to them
        $this->itManager = User::create([
            'name'     => 'IT Manager',
            'email'    => 'it@test.com',
            'password' => 'secret',
            'role'     => 'it_manager',
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // HELPER: Creates a request in DB with correct column names
    // KEY FACTS from your code:
    //   - column is 'created_by' (not 'user_id')
    //   - status values are French: 'en_attente', 'en_cours', 'termine'
    // ──────────────────────────────────────────────────────────
    private function makeRequest(array $overrides = []): EquipmentRequest
    {
        return EquipmentRequest::create(array_merge([
            'employee_name' => 'Test Employee',
            'department'    => 'Finance',
            'position'      => 'employe',
            'status'        => 'en_attente',
            'created_by'    => $this->hr->id,
        ], $overrides));
    }

    // ──────────────────────────────────────────────────────────
    // TEST 1: HR can view their dashboard
    // ──────────────────────────────────────────────────────────
    public function test_hr_can_view_dashboard(): void
    {
        $response = $this->actingAs($this->hr)->get('/hr/dashboard');

        $response->assertStatus(200);
    }

    // ──────────────────────────────────────────────────────────
    // TEST 2: HR can view the create form
    // ──────────────────────────────────────────────────────────
    public function test_hr_can_view_create_form(): void
    {
        $response = $this->actingAs($this->hr)->get('/hr/create');

        $response->assertStatus(200);
    }

    // ──────────────────────────────────────────────────────────
    // TEST 3: HR can create a new equipment request
    // FIX: Route is POST /hr/store (NOT /hr/requests)
    //      position must be one of: employe, apprenti, stagiaire
    //      status is set to 'en_attente' by the controller
    // ──────────────────────────────────────────────────────────
    public function test_hr_can_create_equipment_request(): void
    {
        $response = $this->actingAs($this->hr)->post('/hr/store', [
            'employee_name' => 'Ali Benali',
            'department'    => 'Finance',
            'position'      => 'employe', // must be: employe | apprenti | stagiaire
        ]);

        // Controller redirects to route('hr.requests') on success
        $response->assertRedirect('/hr/requests');

        // Record must be in the database with correct values
        $this->assertDatabaseHas('equipment_requests', [
            'employee_name' => 'Ali Benali',
            'department'    => 'Finance',
            'position'      => 'employe',
            'status'        => 'en_attente',
            'created_by'    => $this->hr->id,
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // TEST 4: Creating a request notifies the IT Manager
    // WHY: store() does Notification::create() for the IT Manager
    //      with type = 'new_request'
    // ──────────────────────────────────────────────────────────
    public function test_creating_request_notifies_it_manager(): void
    {
        $this->actingAs($this->hr)->post('/hr/store', [
            'employee_name' => 'Karima Slimani',
            'department'    => 'RH',
            'position'      => 'stagiaire',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->itManager->id,
            'type'    => 'new_request',
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // TEST 5: Validation fails if required fields are missing
    // ──────────────────────────────────────────────────────────
    public function test_hr_cannot_create_request_without_required_fields(): void
    {
        $response = $this->actingAs($this->hr)->post('/hr/store', []);

        $response->assertSessionHasErrors(['employee_name', 'department', 'position']);
    }

    // ──────────────────────────────────────────────────────────
    // TEST 6: Validation fails with invalid position value
    // ──────────────────────────────────────────────────────────
    public function test_hr_cannot_create_request_with_invalid_position(): void
    {
        $response = $this->actingAs($this->hr)->post('/hr/store', [
            'employee_name' => 'Ali',
            'department'    => 'IT',
            'position'      => 'director', // NOT in: employe, apprenti, stagiaire
        ]);

        $response->assertSessionHasErrors('position');
    }

    // ──────────────────────────────────────────────────────────
    // TEST 7: HR can view their requests list
    // ──────────────────────────────────────────────────────────
    public function test_hr_can_view_requests_list(): void
    {
        $this->makeRequest(['employee_name' => 'Sara Mansouri']);

        $response = $this->actingAs($this->hr)->get('/hr/requests');

        $response->assertStatus(200);
        $response->assertSee('Sara Mansouri');
    }

    // ──────────────────────────────────────────────────────────
    // TEST 8: HR only sees THEIR OWN requests
    // ──────────────────────────────────────────────────────────
    public function test_hr_only_sees_own_requests(): void
    {
        // Another HR user
        $otherHR = User::create([
            'name' => 'Other HR', 'email' => 'other@test.com',
            'password' => 'secret', 'role' => 'hr',
        ]);

        $this->makeRequest(['employee_name' => 'My Employee']);

        EquipmentRequest::create([
            'employee_name' => 'Other Employee',
            'department'    => 'Sales',
            'position'      => 'employe',
            'status'        => 'en_attente',
            'created_by'    => $otherHR->id,
        ]);

        $response = $this->actingAs($this->hr)->get('/hr/requests');

        $response->assertSee('My Employee');
        $response->assertDontSee('Other Employee');
    }

    // ──────────────────────────────────────────────────────────
    // TEST 9: HR can view edit form for a pending request
    // ──────────────────────────────────────────────────────────
    public function test_hr_can_view_edit_form_for_pending_request(): void
    {
        $request = $this->makeRequest();

        $response = $this->actingAs($this->hr)->get("/hr/edit/{$request->id}");

        $response->assertStatus(200);
    }

    // ──────────────────────────────────────────────────────────
    // TEST 10: HR cannot access edit form for in-progress request
    // WHY: edit() checks status !== 'en_attente' → redirect with error
    // ──────────────────────────────────────────────────────────
    public function test_hr_cannot_view_edit_form_for_in_progress_request(): void
    {
        $request = $this->makeRequest(['status' => 'en_cours']);

        $response = $this->actingAs($this->hr)->get("/hr/edit/{$request->id}");

        // Redirects to hr.requests with error flash
        $response->assertRedirect('/hr/requests');
        $response->assertSessionHas('error');
    }

    // ──────────────────────────────────────────────────────────
    // TEST 11: HR can update a pending request
    // FIX: Route is PUT /hr/update/{id} (not /hr/requests/{id})
    // ──────────────────────────────────────────────────────────
    public function test_hr_can_update_pending_request(): void
    {
        $request = $this->makeRequest(['employee_name' => 'Old Name']);

        $response = $this->actingAs($this->hr)->put("/hr/update/{$request->id}", [
            'employee_name' => 'New Name',
            'department'    => 'Marketing',
            'position'      => 'apprenti',
        ]);

        $response->assertRedirect('/hr/requests');

        $this->assertDatabaseHas('equipment_requests', [
            'id'            => $request->id,
            'employee_name' => 'New Name',
            'department'    => 'Marketing',
            'position'      => 'apprenti',
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // TEST 12: HR cannot update an in-progress request
    // WHY: update() checks status !== 'en_attente' → redirects with error
    // ──────────────────────────────────────────────────────────
    public function test_hr_cannot_update_in_progress_request(): void
    {
        $request = $this->makeRequest(['status' => 'en_cours']);

        $response = $this->actingAs($this->hr)->put("/hr/update/{$request->id}", [
            'employee_name' => 'Hacked',
            'department'    => 'IT',
            'position'      => 'employe',
        ]);

        $response->assertRedirect('/hr/requests');
        $response->assertSessionHas('error');

        // DB must NOT have the hacked value
        $this->assertDatabaseMissing('equipment_requests', [
            'id'            => $request->id,
            'employee_name' => 'Hacked',
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // TEST 13: HR can delete a pending request
    // FIX: Route is DELETE /hr/delete/{id} (not /hr/requests/{id})
    // ──────────────────────────────────────────────────────────
    public function test_hr_can_delete_pending_request(): void
    {
        $request = $this->makeRequest();

        $response = $this->actingAs($this->hr)->delete("/hr/delete/{$request->id}");

        $response->assertRedirect('/hr/requests');

        $this->assertDatabaseMissing('equipment_requests', [
            'id' => $request->id,
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // TEST 14: HR CANNOT delete an in-progress request
    // WHY: destroy() blocks when status = 'en_cours' only
    //      (allows delete of 'en_attente' AND 'termine')
    // ──────────────────────────────────────────────────────────
    public function test_hr_cannot_delete_in_progress_request(): void
    {
        $request = $this->makeRequest(['status' => 'en_cours']);

        $this->actingAs($this->hr)->delete("/hr/delete/{$request->id}");

        // Record must still exist
        $this->assertDatabaseHas('equipment_requests', [
            'id'     => $request->id,
            'status' => 'en_cours',
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // TEST 15: HR CAN delete a completed (termine) request
    // WHY: destroy() condition is: status !== 'en_attente' && status !== 'termine'
    //      So 'termine' is ALLOWED to be deleted
    // ──────────────────────────────────────────────────────────
    public function test_hr_can_delete_completed_request(): void
    {
        $request = $this->makeRequest(['status' => 'termine']);

        $response = $this->actingAs($this->hr)->delete("/hr/delete/{$request->id}");

        $response->assertRedirect('/hr/requests');

        $this->assertDatabaseMissing('equipment_requests', [
            'id' => $request->id,
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // TEST 16: HR cannot delete another HR's request
    // ──────────────────────────────────────────────────────────
    public function test_hr_cannot_delete_another_hrs_request(): void
    {
        $otherHR = User::create([
            'name' => 'Other HR', 'email' => 'other@test.com',
            'password' => 'secret', 'role' => 'hr',
        ]);

        $request = EquipmentRequest::create([
            'employee_name' => 'Not Mine',
            'department'    => 'Sales',
            'position'      => 'employe',
            'status'        => 'en_attente',
            'created_by'    => $otherHR->id,
        ]);

        $this->actingAs($this->hr)->delete("/hr/delete/{$request->id}");

        // Must still be in DB
        $this->assertDatabaseHas('equipment_requests', [
            'id' => $request->id,
        ]);
    }
}