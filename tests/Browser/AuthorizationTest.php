<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;

class AuthorizationTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_hr_cannot_access_it_manager_routes()
    {
        $user = User::factory()->create([
            'role' => 'hr',
            'password' => bcrypt('password123'),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/it-manager/dashboard')
                    ->assertPathIsNot('/it-manager/dashboard');
        });
    }

    public function test_technician_cannot_access_hr_routes()
    {
        $user = User::factory()->create([
            'role' => 'technician',
            'password' => bcrypt('password123'),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/hr/dashboard')
                    ->assertPathIsNot('/hr/dashboard');
        });
    }

    public function test_guest_cannot_access_protected_routes()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/hr/dashboard')
                    ->assertPathIsNot('/hr/dashboard');
            
            $browser->visit('/it-manager/dashboard')
                    ->assertPathIsNot('/it-manager/dashboard');
                    
            $browser->visit('/technician/dashboard')
                    ->assertPathIsNot('/technician/dashboard');
        });
    }
}