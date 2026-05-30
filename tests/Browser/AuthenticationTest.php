<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;


class AuthenticationTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected function createUser(string $role, array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => $role,
            'password' => bcrypt('password123'),
        ], $overrides));
    }

    public function test_login_page_loads()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSee('Connexion')
                    ->assertSee('Gestion Équipements IT')
                    ->assertPresent('input[name="email"]')
                    ->assertPresent('input[name="password"]')
                    ->assertPresent('button[type="submit"]')
                    ->assertSee('Forgot Password?');
        });
    }

    public function test_forgot_password_page()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/forgot-password')
                    ->assertPathIs('/forgot-password')
                    ->assertSee('Mot de passe oublié')
                    ->assertSee('Réinitialisation de mot de passe')
                    ->assertPresent('input[name="email"]')
                    ->assertSee('Retour à la connexion');
        });
    }

    public function test_successful_login_as_hr()
    {
        $user = $this->createUser('hr', ['name' => 'Test HR']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/')
                    ->type('email', $user->email)
                    ->type('password', 'password')
                    ->press('Login')
                    ->assertPathIs('/hr/dashboard')
                    ->assertSee('Bienvenue, Test HR!')
                    ->assertSee('Tableau de bord RH');
        });
    }

    public function test_successful_login_as_it_manager()
    {
        $user = $this->createUser('it_manager', ['name' => 'Test IT']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/')
                    ->type('email', $user->email)
                    ->type('password', 'password')
                    ->press('Login')
                    ->assertPathIs('/it-manager/dashboard')
                    ->assertSee('Bienvenue, Test IT!');
        });
    }

    public function test_successful_login_as_technician()
    {
        $user = $this->createUser('technician', ['name' => 'Test Tech']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/')
                    ->type('email', $user->email)
                    ->type('password', 'password')
                    ->press('Login')
                    ->assertPathIs('/technician/dashboard')
                    ->assertSee('Bienvenue, Test Tech!');
        });
    }

    public function test_login_with_invalid_credentials()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->type('email', 'wrong@example.com')
                    ->type('password', 'wrongpassword')
                    ->press('Login')
                    ->assertPathIs('/');
        });
    }

    public function test_password_visibility_toggle()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->type('password', 'secret123')
                    ->assertAttribute('#password', 'type', 'password')
                    ->click('#togglePassword')
                    ->pause(200)
                    ->assertAttribute('#password', 'type', 'text')
                    ->click('#togglePassword')
                    ->pause(200)
                    ->assertAttribute('#password', 'type', 'password');
        });
    }

    public function test_logout()
    {
        $user = $this->createUser('hr');

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/hr/dashboard')
                    ->press('Déconnexion')
                    ->assertPathIs('/');
        });
    }
}