<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Simple approach: Only create users if they don't exist

        // Create HR User
        User::firstOrCreate(
            ['email' => 'meriem.abbas@gmail.com'],
            [
                'name' => 'Meriem Abbas',
                'password' => Hash::make('password'),
                'role' => 'hr'
            ]
        );

        // Create IT Manager
        User::firstOrCreate(
            ['email' => 'karim.haddad@gmail.com'],
            [
                'name' => 'Karim Haddad',
                'password' => Hash::make('password'),
                'role' => 'it_manager'
            ]
        );

        // Create Technician
        User::firstOrCreate(
            ['email' => 'rachid.benali@gmail.com'],
            [
                'name' => 'Rachid Benali',
                'password' => Hash::make('password'),
                'role' => 'technician'
            ]
        );
    }
}
