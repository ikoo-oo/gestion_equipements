<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\EquipmentRequest;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EquipmentRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $hr;
    private User $technician;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hr = User::create([
            'name' => 'HR User', 'email' => 'hr@test.com',
            'password' => 'secret', 'role' => 'hr',
        ]);
        $this->technician = User::create([
            'name' => 'Tech', 'email' => 'tech@test.com',
            'password' => 'secret', 'role' => 'technician',
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // TEST 1: Can create a request with correct column names
    // ──────────────────────────────────────────────────────────
    public function test_can_create_request_with_correct_fields(): void
    {
        $request = EquipmentRequest::create([
            'employee_name' => 'Ahmed Boudali',
            'department'    => 'IT',
            'position'      => 'employe',
            'status'        => 'en_attente',
            'created_by'    => $this->hr->id,
        ]);

        $this->assertDatabaseHas('equipment_requests', [
            'employee_name' => 'Ahmed Boudali',
            'status'        => 'en_attente',
            'created_by'    => $this->hr->id,
        ]);
        $this->assertEquals('en_attente', $request->status);
    }

    // ──────────────────────────────────────────────────────────
    // TEST 2: creator() relationship works correctly
    // WHY: EquipmentRequest::creator() = belongsTo(User, 'created_by')
    // ──────────────────────────────────────────────────────────
    public function test_creator_relationship_returns_hr_user(): void
    {
        $request = EquipmentRequest::create([
            'employee_name' => 'Test',
            'department'    => 'RH',
            'position'      => 'stagiaire',
            'status'        => 'en_attente',
            'created_by'    => $this->hr->id,
        ]);

        $this->assertEquals('HR User', $request->creator->name);
        $this->assertEquals('hr', $request->creator->role);
        $this->assertEquals($this->hr->id, $request->creator->id);
    }

    // ──────────────────────────────────────────────────────────
    // TEST 3: technician() relationship works correctly
    // WHY: EquipmentRequest::technician() = belongsTo(User, 'assigned_to')
    // ──────────────────────────────────────────────────────────
    public function test_technician_relationship_returns_assigned_technician(): void
    {
        $request = EquipmentRequest::create([
            'employee_name'         => 'Test',
            'department'            => 'IT',
            'position'              => 'employe',
            'status'                => 'en_cours',
            'created_by'            => $this->hr->id,
            'assigned_to'           => $this->technician->id,
            'equipment_description' => 'Laptop',
        ]);

        $this->assertEquals('Tech', $request->technician->name);
        $this->assertEquals('technician', $request->technician->role);
    }

    // ──────────────────────────────────────────────────────────
    // TEST 4: Status color attribute returns correct Bootstrap class
    // WHY: getStatusColorAttribute() is a computed property on the model
    // ──────────────────────────────────────────────────────────
    public function test_status_color_attribute_for_en_attente(): void
    {
        $request = EquipmentRequest::create([
            'employee_name' => 'Test', 'department' => 'IT',
            'position' => 'employe', 'status' => 'en_attente',
            'created_by' => $this->hr->id,
        ]);

        $this->assertEquals('warning', $request->status_color);
    }

    public function test_status_color_attribute_for_en_cours(): void
    {
        $request = EquipmentRequest::create([
            'employee_name' => 'Test', 'department' => 'IT',
            'position' => 'employe', 'status' => 'en_cours',
            'created_by' => $this->hr->id,
        ]);

        $this->assertEquals('info', $request->status_color);
    }

    public function test_status_color_attribute_for_termine(): void
    {
        $request = EquipmentRequest::create([
            'employee_name' => 'Test', 'department' => 'IT',
            'position' => 'employe', 'status' => 'termine',
            'created_by' => $this->hr->id,
        ]);

        $this->assertEquals('success', $request->status_color);
    }

    // ──────────────────────────────────────────────────────────
    // TEST 5: notifications() relationship works
    // ──────────────────────────────────────────────────────────
    public function test_notifications_relationship_works(): void
    {
        $request = EquipmentRequest::create([
            'employee_name' => 'Test', 'department' => 'IT',
            'position' => 'employe', 'status' => 'en_attente',
            'created_by' => $this->hr->id,
        ]);

        // Create 2 notifications for this request
        Notification::create([
            'user_id' => $this->hr->id, 'type' => 'new_request',
            'message' => 'Test', 'request_id' => $request->id,
        ]);
        Notification::create([
            'user_id' => $this->technician->id, 'type' => 'assigned_request',
            'message' => 'Test 2', 'request_id' => $request->id,
        ]);

        $this->assertCount(2, $request->notifications);
    }

    // ──────────────────────────────────────────────────────────
    // TEST 6: User model — createdRequests() relationship
    // ──────────────────────────────────────────────────────────
    public function test_user_created_requests_relationship(): void
    {
        EquipmentRequest::create([
            'employee_name' => 'Req 1', 'department' => 'IT',
            'position' => 'employe', 'status' => 'en_attente',
            'created_by' => $this->hr->id,
        ]);
        EquipmentRequest::create([
            'employee_name' => 'Req 2', 'department' => 'RH',
            'position' => 'stagiaire', 'status' => 'en_attente',
            'created_by' => $this->hr->id,
        ]);

        $this->assertCount(2, $this->hr->createdRequests);
    }

    // ──────────────────────────────────────────────────────────
    // TEST 7: User model — assignedRequests() relationship
    // ──────────────────────────────────────────────────────────
    public function test_user_assigned_requests_relationship(): void
    {
        EquipmentRequest::create([
            'employee_name' => 'Assigned', 'department' => 'IT',
            'position' => 'employe', 'status' => 'en_cours',
            'created_by' => $this->hr->id,
            'assigned_to' => $this->technician->id,
        ]);

        $this->assertCount(1, $this->technician->assignedRequests);
    }
}