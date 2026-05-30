<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\EquipmentRequest;

class TechnicianWorkflowTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected function createTechnician(): User
    {
        return User::factory()->create([
            'role' => 'technician',
            'name' => 'Tech User',
            'password' => bcrypt('password123'),
        ]);
    }

    protected function createHRUser(): User
    {
        return User::factory()->create([
            'role' => 'hr',
            'name' => 'HR User',
            'password' => bcrypt('password123'),
        ]);
    }

    public function test_technician_dashboard()
    {
        $user = $this->createTechnician();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/technician/dashboard')
                    ->assertSee('Bienvenue, Tech User!')
                    ->assertSee('Mes demandes assignées')
                    ->assertSee('En cours')
                    ->assertSee('Terminées ce mois');
        });
    }

    public function test_technician_can_view_assigned_requests()
    {
        $hr = $this->createHRUser();
        $tech = $this->createTechnician();
        
        EquipmentRequest::create([
            'employee_name' => 'Alice Moreau',
            'department' => 'Logistique',
            'position' => 'stagiaire',
            'status' => 'en_cours',
            'equipment_description' => 'PC portable, Casque audio',
            'assigned_to' => $tech->id,
            'created_by' => $hr->id,
            'deadline' => now()->addDays(3),
        ]);

        $this->browse(function (Browser $browser) use ($tech) {
            $browser->loginAs($tech)
                    ->visit('/technician/requests')
                    ->assertSee('Mes demandes assignées')
                    ->assertSee('Alice Moreau')
                    ->assertSee('Logistique')
                    ->assertSee('PC portable')
                    ->assertSee('En cours')
                    ->assertPresent('a:contains("Détails")');
        });
    }

    public function test_technician_can_view_request_details()
    {
        $hr = $this->createHRUser();
        $tech = $this->createTechnician();
        
        $request = EquipmentRequest::create([
            'employee_name' => 'Bob Martin',
            'department' => 'IT',
            'position' => 'employe',
            'status' => 'en_cours',
            'equipment_description' => "PC portable Dell XPS 15\nÉcran 24 pouces\nClavier sans fil",
            'assigned_to' => $tech->id,
            'created_by' => $hr->id,
            'deadline' => now()->addWeek(),
        ]);

        $this->browse(function (Browser $browser) use ($tech, $request) {
            $browser->loginAs($tech)
                    ->visit('/technician/requests')
                    ->clickLink('Détails')
                    ->assertPathIs('/technician/details/' . $request->id)
                    ->assertSee('Détails de la demande')
                    ->assertSee('Bob Martin')
                    ->assertSee('IT')
                    ->assertSee('PC portable Dell XPS 15')
                    ->assertSee('Date limite')
                    ->assertSee('En cours');
        });
    }

    public function test_technician_can_complete_request()
    {
        $hr = $this->createHRUser();
        $tech = $this->createTechnician();
        
        $request = EquipmentRequest::create([
            'employee_name' => 'Claire Dubois',
            'department' => 'RH',
            'position' => 'stagiaire',
            'status' => 'en_cours',
            'equipment_description' => 'Souris optique',
            'assigned_to' => $tech->id,
            'created_by' => $hr->id,
        ]);

        $this->browse(function (Browser $browser) use ($tech, $request) {
            $browser->loginAs($tech)
                    ->visit('/technician/details/' . $request->id)
                    ->assertSee('Marquer comme terminé')
                    ->press('Marquer comme terminé')
                    ->acceptDialog()
                    ->assertPathIs('/technician/requests')
                    ->assertSee('Terminé');
        });
    }

    public function test_completed_request_shows_success_message()
    {
        $hr = $this->createHRUser();
        $tech = $this->createTechnician();
        
        $request = EquipmentRequest::create([
            'employee_name' => 'Done User',
            'department' => 'Finance',
            'position' => 'employe',
            'status' => 'termine',
            'assigned_to' => $tech->id,
            'created_by' => $hr->id,
        ]);

        $this->browse(function (Browser $browser) use ($tech, $request) {
            $browser->loginAs($tech)
                    ->visit('/technician/details/' . $request->id)
                    ->assertSee('Demande terminée!')
                    ->assertSee('Cette demande a été marquée comme terminée.');
        });
    }

    public function test_technician_empty_state()
    {
        $tech = $this->createTechnician();

        $this->browse(function (Browser $browser) use ($tech) {
            $browser->loginAs($tech)
                    ->visit('/technician/requests')
                    ->assertSee('Aucune demande assignée')
                    ->assertSee('Vous n\'avez pas encore de demandes à traiter');
        });
    }

    public function test_technician_dashboard_stats()
    {
        $hr = $this->createHRUser();
        $tech = $this->createTechnician();

        EquipmentRequest::create(['employee_name' => 'A', 'department' => 'IT', 'position' => 'employe', 'status' => 'en_cours', 'assigned_to' => $tech->id, 'created_by' => $hr->id]);
        EquipmentRequest::create(['employee_name' => 'B', 'department' => 'IT', 'position' => 'employe', 'status' => 'termine', 'assigned_to' => $tech->id, 'created_by' => $hr->id, 'updated_at' => now()]);
        EquipmentRequest::create(['employee_name' => 'C', 'department' => 'IT', 'position' => 'employe', 'status' => 'termine', 'assigned_to' => $tech->id, 'created_by' => $hr->id, 'updated_at' => now()->subMonth()]);

        $this->browse(function (Browser $browser) use ($tech) {
            $browser->loginAs($tech)
                    ->visit('/technician/dashboard')
                    ->assertSee('1')
                    ->assertSee('1')
                    ->assertSee('3');
        });
    }
}