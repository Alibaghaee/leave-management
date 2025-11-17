<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $managers = Employee::factory()->count(10)->create(['role' => 'manager']);
        $hr = Employee::factory()->create(['role' => 'hr']);
        $ceo = Employee::factory()->create(['role' => 'ceo']);

        Employee::factory()
            ->count(1000)
            ->create()
            ->each(function ($emp) use ($managers) {
                $emp->manager_id = $managers->random()->id;
                $emp->save();
            });
    }
}
