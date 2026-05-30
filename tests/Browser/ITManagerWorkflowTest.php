<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\EquipmentRequest;

class ITManagerWorkflowTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected function createITManager(): User
    {
        return User::factory()->create([
            'role' => 'it_manager',
            'name' => 'IT Manager',
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

    protected function createTechnician(): User
    {
        return User::factory()->create([
            'role' => 'technician',
            'name' => 'Technician User',
            'password' => bcrypt('password123'),
        ]);
    }

    public function test_it_manager_dashboard()
    {
        $user = $this->createITManager();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/it-manager/dashboard')
                    ->assertSee('Bienvenue, IT Manager!')
                    ->assertSee('Demandes reçues')
                    ->assertSee('Demandes assignées')
                    ->assertSee('Nouvelles demandes')
                    ->assertSee('En cours de traitement');
        });
    }

    public function test_it_manager_can_view_received_requests()
    {
        $hr = $this->createHRUser();
        $itManager = $this->createITManager();
        
        EquipmentRequest::create([
            'employee_name' => 'Pierre Durand',
            'department' => 'Production',
            'position' => 'apprenti',
            'status' => 'en_attente',
            'created_by' => $hr->id,
        ]);

        $this->browse(function (Browser $browser) use ($itManager) {
            $browser->loginAs($itManager)
                    ->visit('/it-manager/received')
                    ->assertSee('Demandes reçues du département RH')
                    ->assertSee('Pierre Durand')
                    ->assertSee('Production')
                    ->assertSee('Apprenti')
                    ->assertPresent('a:contains("Assigner")');
        });
    }

    public function test_it_manager_can_assign_request()
    {
        $hr = $this->createHRUser();
        $itManager = $this->createITManager();
        $technician = $this->createTechnician();
        
        $request = EquipmentRequest::create([
            'employee_name' => 'Sophie Bernard',
            'department' => 'Commercial',
            'position' => 'employe',
            'status' => 'en_attente',
            'created_by' => $hr->id,
        ]);

        $this->browse(function (Browser $browser) use ($itManager, $technician, $request) {
            $browser->loginAs($itManager)
                    ->visit('/it-manager/received')
                    ->clickLink('Assigner')
                    ->assertPathIs('/it-manager/assign/' . $request->id)
                    ->assertSee('Assigner la demande au technicien')
                    ->assertSee('Sophie Bernard')
                    ->assertSee('Commercial')
                    ->type('equipment_description', 'PC portable Dell XPS 15, Écran 24 pouces')
                    ->type('deadline', now()->addWeek()->format('Y-m-d'))
                    ->select('assigned_to', (string) $technician->id)
                    ->press('Assigner au technicien')
                    ->assertPathIs('/it-manager/dashboard')
                    ->assertSee('Demande assignée avec succès au technicien!');
        });
    }

    public function test_it_manager_assigned_requests_list()
    {
        $hr = $this->createHRUser();
        $itManager = $this->createITManager();
        $technician = $this->createTechnician();
        
        EquipmentRequest::create([
            'employee_name' => 'Lucas Petit',
            'department' => 'IT',
            'position' => 'employe',
            'status' => 'en_cours',
            'equipment_description' => 'Clavier sans fil, Souris optique',
            'assigned_to' => $technician->id,
            'created_by' => $hr->id,
        ]);

        $this->browse(function (Browser $browser) use ($itManager) {
            $browser->loginAs($itManager)
                    ->visit('/it-manager/assigned')
                    ->assertSee('Demandes assignées aux techniciens')
                    ->assertSee('Lucas Petit')
                    ->assertSee('Clavier sans fil')
                    ->assertSee('Technician User')
                    ->assertSee('En cours');
        });
    }

    public function test_empty_state_for_no_received_requests()
    {
        $itManager = $this->createITManager();

        $this->browse(function (Browser $browser) use ($itManager) {
            $browser->loginAs($itManager)
                    ->visit('/it-manager/received')
                    ->assertSee('Aucune demande en attente')
                    ->assertSee('Toutes les demandes ont été assignées!');
        });
    }

    public function test_it_manager_dashboard_stats()
    {
        $hr = $this->createHRUser();
        $itManager = $this->createITManager();
        $technician = $this->createTechnician();

        EquipmentRequest::create(['employee_name' => 'A', 'department' => 'IT', 'position' => 'employe', 'status' => 'en_attente', 'created_by' => $hr->id]);
        EquipmentRequest::create(['employee_name' => 'B', 'department' => 'IT', 'position' => 'employe', 'status' => 'en_cours', 'assigned_to' => $technician->id, 'created_by' => $hr->id]);
        EquipmentRequest::create(['employee_name' => 'C', 'department' => 'IT', 'position' => 'employe', 'status' => 'termine', 'created_by' => $hr->id, 'updated_at' => now()]);

        $this->browse(function (Browser $browser) use ($itManager) {
            $browser->loginAs($itManager)
                    ->visit('/it-manager/dashboard')
                    ->assertSee('1')
                    ->assertSee('1')
                    ->assertSee('1');
        });
    }
}