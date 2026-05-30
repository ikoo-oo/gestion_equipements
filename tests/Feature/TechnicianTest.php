<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\EquipmentRequest;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TechnicianTest extends TestCase
{
    use RefreshDatabase;

    private User $hr;
    private User $technician;
    private EquipmentRequest $assignedRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hr = User::create([
            'name' => 'HR', 'email' => 'hr@test.com',
            'password' => 'secret', 'role' => 'hr',
        ]);
        $this->technician = User::create([
            'name' => 'Tech', 'email' => 'tech@test.com',
            'password' => 'secret', 'role' => 'technician',
        ]);

        // Pre-create a request assigned to our technician
        // KEY: column is 'assigned_to' (not 'technician_id')
        //      status is 'en_cours' (not 'in_progress')
        $this->assignedRequest = EquipmentRequest::create([
            'employee_name'         => 'Assigned Employee',
            'department'            => 'Accounting',
            'position'              => 'employe',
            'status'                => 'en_cours',
            'created_by'            => $this->hr->id,
            'assigned_to'           => $this->technician->id,
            'equipment_description' => 'Dell Laptop + Mouse',
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // TEST 1: Technician can view their dashboard
    // ──────────────────────────────────────────────────────────
    public function test_technician_can_view_dashboard(): void
    {
        $response = $this->actingAs($this->technician)->get('/technician/dashboard');

        $response->assertStatus(200);
    }

    // ──────────────────────────────────────────────────────────
    // TEST 2: Technician sees ONLY their assigned requests
    // WHY: requests() queries WHERE assigned_to = Auth::id()
    // ──────────────────────────────────────────────────────────
    public function test_technician_sees_only_their_assigned_requests(): void
    {
        // Unassigned pending request — should NOT appear
        EquipmentRequest::create([
            'employee_name' => 'Someone Else',
            'department'    => 'Sales',
            'position'      => 'employe',
            'status'        => 'en_attente',
            'created_by'    => $this->hr->id,
        ]);

        $response = $this->actingAs($this->technician)->get('/technician/requests');

        $response->assertStatus(200);
        $response->assertSee('Assigned Employee');    // theirs ✅
        $response->assertDontSee('Someone Else');     // not theirs ✅
    }

    // ──────────────────────────────────────────────────────────
    // TEST 3: Technician can view details of their assigned request
    // ──────────────────────────────────────────────────────────
    public function test_technician_can_view_details_of_own_request(): void
    {
        $response = $this->actingAs($this->technician)
            ->get("/technician/details/{$this->assignedRequest->id}");

        $response->assertStatus(200);
    }

    // ──────────────────────────────────────────────────────────
    // TEST 4: Technician CANNOT view details of another tech's request
    // WHY: details() checks assigned_to !== Auth::id() → redirect with error
    // ──────────────────────────────────────────────────────────
    public function test_technician_cannot_view_details_of_others_request(): void
    {
        $otherTech = User::create([
            'name' => 'Other Tech', 'email' => 'other@test.com',
            'password' => 'secret', 'role' => 'technician',
        ]);
        $otherRequest = EquipmentRequest::create([
            'employee_name' => 'Their Employee',
            'department'    => 'IT', 'position' => 'employe',
            'status'        => 'en_cours',
            'created_by'    => $this->hr->id,
            'assigned_to'   => $otherTech->id,
        ]);

        $response = $this->actingAs($this->technician)
            ->get("/technician/details/{$otherRequest->id}");

        // details() redirects with error message
        $response->assertRedirect('/technician/requests');
        $response->assertSessionHas('error');
    }

    // ──────────────────────────────────────────────────────────
    // TEST 5: Technician can mark their request as completed
    // WHY: complete() changes status to 'termine'
    //      Route: POST /technician/complete/{id}
    // ──────────────────────────────────────────────────────────
    public function test_technician_can_mark_request_as_completed(): void
    {
        $response = $this->actingAs($this->technician)
            ->post("/technician/complete/{$this->assignedRequest->id}");

        $response->assertRedirect('/technician/requests');

        $this->assertDatabaseHas('equipment_requests', [
            'id'     => $this->assignedRequest->id,
            'status' => 'termine',  // French status from your model
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // TEST 6: Completing a request notifies the HR
    // WHY: complete() creates Notification for created_by user
    //      with type = 'request_completed'
    // ──────────────────────────────────────────────────────────
    public function test_completing_request_creates_notification_for_hr(): void
    {
        $this->actingAs($this->technician)
            ->post("/technician/complete/{$this->assignedRequest->id}");

        $this->assertDatabaseHas('notifications', [
            'user_id'    => $this->hr->id,              // notifies the HR who created it
            'type'       => 'request_completed',
            'request_id' => $this->assignedRequest->id,
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // TEST 7: Completing deletes old assigned_request notification
    // WHY: complete() calls Notification::whereIn('type', [...])
    //      ->delete() to clean up old notifications
    // ──────────────────────────────────────────────────────────
    public function test_completing_request_deletes_assigned_notification(): void
    {
        // Create the 'assigned_request' notification (created when IT Manager assigned)
        Notification::create([
            'user_id'    => $this->technician->id,
            'type'       => 'assigned_request',
            'message'    => 'Demande assignée',
            'request_id' => $this->assignedRequest->id,
        ]);

        $this->actingAs($this->technician)
            ->post("/technician/complete/{$this->assignedRequest->id}");

        // Old 'assigned_request' notification must be cleaned up
        $this->assertDatabaseMissing('notifications', [
            'request_id' => $this->assignedRequest->id,
            'type'       => 'assigned_request',
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // TEST 8: Technician CANNOT complete another tech's request
    // WHY: complete() checks assigned_to !== Auth::id()
    //      → redirects with error (302), does NOT use abort(403)
    // ──────────────────────────────────────────────────────────
    public function test_technician_cannot_complete_others_request(): void
    {
        $otherTech = User::create([
            'name' => 'Other Tech', 'email' => 'other@test.com',
            'password' => 'secret', 'role' => 'technician',
        ]);
        $otherRequest = EquipmentRequest::create([
            'employee_name' => 'Their Employee',
            'department'    => 'IT', 'position' => 'employe',
            'status'        => 'en_cours',
            'created_by'    => $this->hr->id,
            'assigned_to'   => $otherTech->id,
        ]);

        $response = $this->actingAs($this->technician)
            ->post("/technician/complete/{$otherRequest->id}");

        // complete() redirects (not abort), status stays 'en_cours'
        $response->assertRedirect('/technician/requests');
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('equipment_requests', [
            'id'     => $otherRequest->id,
            'status' => 'en_cours',  // unchanged
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // TEST 9: Technician CANNOT complete an already-completed request
    // WHY: complete() checks status !== 'en_cours' → redirect with error
    // ──────────────────────────────────────────────────────────
    public function test_technician_cannot_complete_already_completed_request(): void
    {
        $doneRequest = EquipmentRequest::create([
            'employee_name' => 'Done Employee',
            'department'    => 'IT', 'position' => 'employe',
            'status'        => 'termine',  // already done!
            'created_by'    => $this->hr->id,
            'assigned_to'   => $this->technician->id,
        ]);

        $response = $this->actingAs($this->technician)
            ->post("/technician/complete/{$doneRequest->id}");

        $response->assertRedirect('/technician/requests');
        $response->assertSessionHas('error');
    }
}