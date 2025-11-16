<?php

namespace Database\Factories;

use App\Models\LeaveRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement(['draft','pending_hr','pending_manager','pending_ceo','approved','rejected','due_date']);
        return [
            'employee_id' => 1,
            'leave_type' => $this->faker->randomElement(['annual','sick','unpaid','other']),
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'time_start' => null,
            'time_end' => null,
            'reason' => $this->faker->sentence(6),
            'approver_id' => null,
            'status' => $status,
            'current_stage_id' => 1,
            'rejection_reason' => null,
            'count_days' => $this->faker->numberBetween(1, 10),
        ];
    }
}
