<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Employee;
use App\Models\Stage;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class PipelineApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hr = Employee::factory()->create(['role' => 'hr']);
        $this->manager = Employee::factory()->create(['role' => 'manager']);
        $this->otherManager = Employee::factory()->create(['role' => 'manager']);
        $this->ceo = Employee::factory()->create(['role' => 'ceo']);
        $this->employee = Employee::factory()->create([
            'role' => 'employee',
            'manager_id' => $this->manager->id,
            'leave_balance' => 15.0
        ]);

        $this->hrStage = Stage::create(['name' => 'HR Review', 'role' => 'hr', 'order' => 1, 'min_days' => 0]);
        $this->managerStage = Stage::create(['name' => 'Manager Review', 'role' => 'manager', 'order' => 2, 'min_days' => 0]);
        $this->ceoStage = Stage::create(['name' => 'CEO Approval', 'role' => 'ceo', 'order' => 3, 'min_days' => 5]);

        $this->hrStage->next_stage_id = $this->managerStage->id;
        $this->hrStage->save();
        $this->managerStage->next_stage_id = $this->ceoStage->id;
        $this->managerStage->save();

        $this->userEmployee = User::factory()->create();
        $this->userEmployee->employee_id = $this->employee->id;
        $this->userEmployee->save();

        $this->userManager = User::factory()->create();
        $this->userManager->employee_id = $this->manager->id;
        $this->userManager->save();

        $this->userOtherManager = User::factory()->create();
        $this->userOtherManager->employee_id = $this->otherManager->id;
        $this->userOtherManager->save();

        $this->userHr = User::factory()->create();
        $this->userHr->employee_id = $this->hr->id;
        $this->userHr->save();

        $this->userCeo = User::factory()->create();
        $this->userCeo->employee_id = $this->ceo->id;
        $this->userCeo->save();
    }

    public function test_employee_cannot_approve()
    {
        $this->actingAs($this->userEmployee, 'sanctum');

        $lr = LeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type' => 'annual',
            'start_date' => Carbon::today()->addDays(2)->toDateString(),
            'end_date' => Carbon::today()->addDays(2)->toDateString(),
            'status' => 'pending_hr',
            'current_stage_id' => $this->hrStage->id,
            'days_count' => 1,
        ]);

        $resp = $this->postJson("/api/v1/leave-requests/{$lr->id}/approve", ['comment' => 'I approve']);
        $resp->assertStatus(403);
    }

    public function test_hr_can_approve_and_advance_to_manager()
    {
        $this->actingAs($this->userHr, 'sanctum');

        $lr = LeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type' => 'annual',
            'start_date' => Carbon::today()->addDays(3)->toDateString(),
            'end_date' => Carbon::today()->addDays(3)->toDateString(),
            'status' => 'pending_hr',
            'current_stage_id' => $this->hrStage->id,
            'days_count' => 1,
        ]);

        $resp = $this->postJson("/api/v1/leave-requests/{$lr->id}/approve", ['comment' => 'hr ok', 'idempotency_key' => 'hr-p1']);
        $resp->assertStatus(200);
        $this->assertDatabaseHas('leave_requests', [
            'id' => $lr->id,
            'status' => 'pending_manager',
            'current_stage_id' => $this->managerStage->id,
        ]);
    }

    public function test_manager_can_only_approve_subordinate()
    {

        $lr = LeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type' => 'annual',
            'start_date' => Carbon::today()->addDays(4)->toDateString(),
            'end_date' => Carbon::today()->addDays(4)->toDateString(),
            'status' => 'pending_manager',
            'current_stage_id' => $this->managerStage->id,
            'days_count' => 1,
        ]);


        $this->actingAs($this->userOtherManager, 'sanctum');
        $resp = $this->postJson("/api/v1/leave-requests/{$lr->id}/approve", ['comment' => 'other mgr try', 'idempotency_key' => 'o-mgr']);
        $resp->assertStatus(422);


        $this->actingAs($this->userManager, 'sanctum');
        $resp2 = $this->postJson("/api/v1/leave-requests/{$lr->id}/approve", ['comment' => 'manager ok', 'idempotency_key' => 'mgr-p1']);
        $resp2->assertStatus(200);
        $this->assertDatabaseHas('leave_requests', [
            'id' => $lr->id,
            'status' => 'approved',
        ]);
    }

    public function test_ceo_required_for_long_leaves_but_skipped_for_short()
    {

        $short = LeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type' => 'annual',
            'start_date' => Carbon::today()->addDays(6)->toDateString(),
            'end_date' => Carbon::today()->addDays(7)->toDateString(),
            'status' => 'pending_hr',
            'current_stage_id' => $this->hrStage->id,
            'days_count' => 2,
        ]);

        $this->actingAs($this->userHr, 'sanctum');
        $this->postJson("/api/v1/leave-requests/{$short->id}/approve", ['idempotency_key' => 'hr-short']);
        $this->actingAs($this->userManager, 'sanctum');
        $this->postJson("/api/v1/leave-requests/{$short->id}/approve", ['idempotency_key' => 'mgr-short']);
        $this->assertDatabaseHas('leave_requests', ['id' => $short->id, 'status' => 'approved']);


        $long = LeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type' => 'annual',
            'start_date' => Carbon::today()->addDays(10)->toDateString(),
            'end_date' => Carbon::today()->addDays(15)->toDateString(),
            'status' => 'pending_hr',
            'current_stage_id' => $this->hrStage->id,
            'days_count' => 6,
        ]);

        $this->actingAs($this->userHr, 'sanctum');
        $this->postJson("/api/v1/leave-requests/{$long->id}/approve", ['idempotency_key' => 'hr-long']);

        $this->actingAs($this->userManager, 'sanctum');
        $this->postJson("/api/v1/leave-requests/{$long->id}/approve", ['idempotency_key' => 'mgr-long']);

        $this->assertDatabaseHas('leave_requests', ['id' => $long->id, 'status' => 'pending_ceo', 'current_stage_id' => $this->ceoStage->id]);


        $this->actingAs($this->userCeo, 'sanctum');
        $this->postJson("/api/v1/leave-requests/{$long->id}/approve", ['idempotency_key' => 'ceo-long']);
        $this->assertDatabaseHas('leave_requests', ['id' => $long->id, 'status' => 'approved']);
    }

    public function test_reject_stops_pipeline_and_no_balance_change()
    {
        $initialBalance = $this->employee->leave_balance;

        $lr = LeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type' => 'annual',
            'start_date' => Carbon::today()->addDays(20)->toDateString(),
            'end_date' => Carbon::today()->addDays(22)->toDateString(),
            'status' => 'pending_hr',
            'current_stage_id' => $this->hrStage->id,
            'days_count' => 3,
        ]);

        $this->actingAs($this->userHr, 'sanctum');
        $this->postJson("/api/v1/leave-requests/{$lr->id}/reject", ['comment' => 'not allowed', 'idempotency_key' => 'hr-rej']);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $lr->id,
            'status' => 'rejected',
        ]);

        $this->employee->refresh();
        $this->assertEquals($initialBalance, $this->employee->leave_balance);
    }
}
