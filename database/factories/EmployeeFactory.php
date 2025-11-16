<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'name_full' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'position' => $this->faker->jobTitle,
            'manager_id' => null,
            'role' => $this->faker->randomElement(['employee','manager','hr','ceo']),
            'leave_balance' => $this->faker->numberBetween(0, 30),
        ];
    }
}
