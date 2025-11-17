<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Employee;
use App\Models\Stage;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;

class AggregationTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_aggregation_upserts_summary()
    {
        $hr = Employee::factory()->create(['role' => 'hr']);
        $userHr = User::factory()->create();
        $userHr->employee_id = $hr->id;
        $userHr->save();

        $emp = Employee::factory()->create(['leave_balance' => 20]);
        $date = Carbon::today()->toDateString();

        LeaveRequest::create([
            'employee_id' => $emp->id,
            'leave_type' => 'annual',
            'start_date' => $date,
            'end_date' => $date,
            'status' => 'pending_manager',
            'current_stage_id' => null,
            'days_count' => 1,
        ]);

        LeaveRequest::create([
            'employee_id' => $emp->id,
            'leave_type' => 'annual',
            'start_date' => $date,
            'end_date' => $date,
            'status' => 'approved',
            'current_stage_id' => null,
            'days_count' => 2,
        ]);

        $this->actingAs($userHr, 'sanctum');
        $resp = $this->postJson('/api/v1/employee-leave-summaries/daily/aggregate', ['date' => $date]);
        $resp->assertStatus(200);

        $this->assertDatabaseHas('employee_leave_summary_dailies', [
            'employee_id' => $emp->id,
            'date' => $date,
            'leave_type' => 'annual',
            'requested_days' => 3,
            'approved_days' => 2,
            'pending_days' => 1,
        ]);
    }
}
