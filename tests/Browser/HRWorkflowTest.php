<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\EquipmentRequest;

class HRWorkflowTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected function createHRUser(): User
    {
        return User::factory()->create([
            'role' => 'hr',
            'name' => 'HR User',
            'password' => bcrypt('password123'),
        ]);
    }

    public function test_hr_dashboard_displays_correctly()
    {
        $user = $this->createHRUser();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/hr/dashboard')
                    ->assertSee('Bienvenue, HR User!')
                    ->assertSee('Tableau de bord RH')
                    ->assertSee('Créer une demande')
                    ->assertSee('Voir les demandes')
                    ->assertSee('Total demandes')
                    ->assertSee('En attente')
                    ->assertSee('En cours')
                    ->assertSee('Terminées');
        });
    }

    public function test_hr_can_create_request()
    {
        $user = $this->createHRUser();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/hr/create')
                    ->assertSee('Créer une demande d\'équipement')
                    ->type('employee_name', 'Jean Dupont')
                    ->select('department', 'IT')
                    ->select('position', 'employe')
                    ->press('Créer la demande')
                    ->assertPathIs('/hr/requests')
                    ->assertSee('Jean Dupont')
                    ->assertSee('En attente');
        });
    }

    public function test_hr_create_request_validation()
    {
        $user = $this->createHRUser();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/hr/create')
                    ->press('Créer la demande')
                    ->assertPathIs('/hr/create')
                    ->assertSee('Le nom de l\'employé est requis.');
        });
    }

    public function test_hr_can_view_requests_list()
    {
        $user = $this->createHRUser();
        
        EquipmentRequest::create([
            'employee_name' => 'Marie Martin',
            'department' => 'RH',
            'position' => 'stagiaire',
            'status' => 'en_attente',
            'created_by' => $user->id,
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/hr/requests')
                    ->assertSee('Mes demandes d\'équipements')
                    ->assertSee('Marie Martin')
                    ->assertSee('RH')
                    ->assertSee('Stagiaire')
                    ->assertPresent('.badge.bg-warning');
        });
    }

    public function test_hr_can_edit_pending_request()
    {
        $user = $this->createHRUser();
        
        $request = EquipmentRequest::create([
            'employee_name' => 'Old Name',
            'department' => 'Finance',
            'position' => 'employe',
            'status' => 'en_attente',
            'created_by' => $user->id,
        ]);

        $this->browse(function (Browser $browser) use ($user, $request) {
            $browser->loginAs($user)
                    ->visit('/hr/requests')
                    ->assertSee('Old Name')
                    ->clickLink('Modifier')
                    ->assertPathIs('/hr/edit/' . $request->id)
                    ->assertInputValue('employee_name', 'Old Name')
                    ->clear('employee_name')
                    ->type('employee_name', 'New Name')
                    ->select('department', 'Marketing')
                    ->press('Enregistrer les modifications')
                    ->assertPathIs('/hr/requests')
                    ->assertSee('New Name')
                    ->assertSee('Marketing');
        });
    }

    public function test_hr_can_delete_pending_request()
    {
        $user = $this->createHRUser();
        
        EquipmentRequest::create([
            'employee_name' => 'To Delete',
            'department' => 'IT',
            'position' => 'employe',
            'status' => 'en_attente',
            'created_by' => $user->id,
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/hr/requests')
                    ->assertSee('To Delete')
                    ->press('Supprimer')
                    ->acceptDialog()
                    ->assertPathIs('/hr/requests')
                    ->assertDontSee('To Delete');
        });
    }

    public function test_edit_button_hidden_for_non_pending_requests()
    {
        $user = $this->createHRUser();
        
        EquipmentRequest::create([
            'employee_name' => 'In Progress',
            'department' => 'IT',
            'position' => 'employe',
            'status' => 'en_cours',
            'created_by' => $user->id,
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/hr/requests')
                    ->assertSee('In Progress')
                    ->assertDontSeeLink('Modifier');
        });
    }

    public function test_hr_dashboard_stats_are_accurate()
    {
        $user = $this->createHRUser();
        
        EquipmentRequest::create(['employee_name' => 'A', 'department' => 'IT', 'position' => 'employe', 'status' => 'en_attente', 'created_by' => $user->id]);
        EquipmentRequest::create(['employee_name' => 'B', 'department' => 'IT', 'position' => 'employe', 'status' => 'en_cours', 'created_by' => $user->id]);
        EquipmentRequest::create(['employee_name' => 'C', 'department' => 'IT', 'position' => 'employe', 'status' => 'termine', 'created_by' => $user->id]);
        EquipmentRequest::create(['employee_name' => 'D', 'department' => 'IT', 'position' => 'employe', 'status' => 'termine', 'created_by' => $user->id]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/hr/dashboard')
                    ->assertSee('4')
                    ->assertSee('1')
                    ->assertSee('1')
                    ->assertSee('2');
        });
    }
}