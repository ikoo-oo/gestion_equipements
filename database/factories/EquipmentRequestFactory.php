<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\EquipmentRequest;
use App\Models\User;

class EquipmentRequestFactory extends Factory
{
    protected $model = EquipmentRequest::class;

    public function definition(): array
    {
        return [
            'employee_name' => fake()->name(),
            'department' => fake()->randomElement(['IT', 'RH', 'Finance', 'Commercial', 'Marketing', 'Production', 'Logistique']),
            'position' => fake()->randomElement(['employe', 'apprenti', 'stagiaire']),
            'status' => 'en_attente',
            'equipment_description' => null,
            'deadline' => null,
            'assigned_to' => null,
            'created_by' => User::factory(),
        ];
    }
}