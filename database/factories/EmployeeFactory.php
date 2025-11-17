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
            'full_name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'position' => $this->faker->jobTitle,
            'manager_id' => null,
            'role' => $this->faker->randomElement(['employee','manager','hr','ceo']),
            'leave_balance' => $this->faker->randomFloat(2, 0, 30),
            'join_date' => $this->faker->date(),
        ];
    }
}
