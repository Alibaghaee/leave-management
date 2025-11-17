<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Stage;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-1 month', '+1 month');
        $end = (clone $start)->modify('+' . random_int(0, 5) . ' days');
        $employee = Employee::inRandomOrder()->first() ?: Employee::factory()->create();
        $stage = Stage::inRandomOrder()->first();
        $days = (float)($end->diff($start)->format('%a')) + 1;
        return [
            'employee_id' => $employee->id,
            'leave_type' => $this->faker->randomElement(['annual', 'sick', 'unpaid', 'other']),
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'start_time' => null,
            'end_time' => null,
            'reason' => $this->faker->sentence(6),
            'approver_id' => null,
            'status' => $this->faker->randomElement(['draft', 'pending_hr', 'pending_manager', 'pending_ceo', 'approved', 'rejected']),
            'current_stage_id' => $stage?->id,
            'rejection_reason' => null,
            'days_count' => $days,
            'meta' => null,
        ];
    }
}
