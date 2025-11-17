<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Employee;
use App\Models\Stage;
use App\Models\LeaveRequest;
use App\Models\LeaveLog;
use Carbon\Carbon;

class LeaveRequestFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ceo = Employee::factory()->create(['role' => 'ceo']);
        $this->hr = Employee::factory()->create(['role' => 'hr']);
        $this->manager = Employee::factory()->create(['role' => 'manager']);
        $this->employee = Employee::factory()->create(['role' => 'employee', 'manager_id' => $this->manager->id, 'leave_balance' => 10.0]);

        $this->hrStage = Stage::create(['name' => 'HR Review', 'role' => 'hr', 'order' => 1, 'min_days' => 0, 'next_stage_id' => null]);
        $this->managerStage = Stage::create(['name' => 'Manager Review', 'role' => 'manager', 'order' => 2, 'min_days' => 0, 'next_stage_id' => null]);
        $this->ceoStage = Stage::create(['name' => 'CEO Approval', 'role' => 'ceo', 'order' => 3, 'min_days' => 5, 'next_stage_id' => null]);

        $this->hrStage->next_stage_id = $this->managerStage->id;
        $this->hrStage->save();
        $this->managerStage->next_stage_id = $this->ceoStage->id;
        $this->managerStage->save();


        $this->userEmployee = User::factory()->create(['email' => 'emp@example.test']);
        $this->userEmployee->employee_id = $this->employee->id;
        $this->userEmployee->save();

        $this->userManager = User::factory()->create(['email' => 'mgr@example.test']);
        $this->userManager->employee_id = $this->manager->id;
        $this->userManager->save();

        $this->userHr = User::factory()->create(['email' => 'hr@example.test']);
        $this->userHr->employee_id = $this->hr->id;
        $this->userHr->save();
    }

    public function test_create_daily_leave_request_success()
    {
        $this->actingAs($this->userEmployee, 'sanctum');

        $start = Carbon::today()->addDays(2)->toDateString();
        $end = Carbon::today()->addDays(3)->toDateString();

        $payload = [
            'start_date' => $start,
            'end_date' => $end,
            'leave_type' => 'annual',
            'current_stage_id' => $this->hrStage->id,
            'reason' => 'Vacation',
        ];

        $resp = $this->postJson('/api/v1/leave-requests', $payload);
        $resp->assertStatus(201);
        $this->assertDatabaseHas('leave_requests', [
            'employee_id' => $this->employee->id,
            'start_date' => $start,
            'end_date' => $end,
            'leave_type' => 'annual',
        ]);
        $body = $resp->json('data');
        $this->assertEquals(2.0, (float)$body['days_count']);
    }

    public function test_create_hourly_leave_request_calculates_fractional_days()
    {
        $this->actingAs($this->userEmployee, 'sanctum');

        $start = Carbon::today()->addDays(1)->toDateString();
        $payload = [
            'start_date' => $start,
            'end_date' => $start,
            'start_time' => '09:00',
            'end_time' => '13:00',
            'leave_type' => 'hourly',
            'current_stage_id' => $this->hrStage->id,
            'reason' => 'Doctor',
        ];

        $resp = $this->postJson('/api/v1/leave-requests', $payload);
        $resp->assertStatus(201);
        $body = $resp->json('data');
        $this->assertEquals(0.5, (float)$body['days_count']);
    }

    public function test_overlap_leave_request_is_rejected()
    {
        $this->actingAs($this->userEmployee, 'sanctum');

        $start = Carbon::today()->addDays(5)->toDateString();
        $end = Carbon::today()->addDays(7)->toDateString();

        LeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type' => 'annual',
            'start_date' => $start,
            'end_date' => $end,
            'status' => 'pending_manager',
            'current_stage_id' => $this->managerStage->id,
            'days_count' => 3,
        ]);

        $payload = [
            'start_date' => Carbon::today()->addDays(6)->toDateString(),
            'end_date' => Carbon::today()->addDays(8)->toDateString(),
            'leave_type' => 'annual',
            'current_stage_id' => $this->hrStage->id,
            'reason' => 'Overlap test',
        ];

        $resp = $this->postJson('/api/v1/leave-requests', $payload);
        $resp->assertStatus(422);
        $resp->assertJsonValidationErrors(['date']);
    }

    public function test_submit_advance_and_final_approve_reduces_leave_balance()
    {
        $this->actingAs($this->userEmployee, 'sanctum');

        $start = Carbon::today()->addDays(10)->toDateString();
        $end = Carbon::today()->addDays(12)->toDateString();

        $resp = $this->postJson('/api/v1/leave-requests', [
            'start_date' => $start,
            'end_date' => $end,
            'leave_type' => 'annual',
            'current_stage_id' => $this->hrStage->id,
            'reason' => 'Long trip',
        ]);
        $resp->assertStatus(201);
        $lrId = $resp->json('data.id');


        $this->actingAs($this->userHr, 'sanctum');
        $approveResp = $this->postJson("/api/v1/leave-requests/{$lrId}/approve", ['comment' => 'ok', 'idempotency_key' => 'hr-1']);
        $approveResp->assertStatus(200);
        $this->assertDatabaseHas('leave_requests', ['id' => $lrId, 'status' => 'pending_manager', 'current_stage_id' => $this->managerStage->id]);


        $this->actingAs($this->userManager, 'sanctum');
        $approveResp2 = $this->postJson("/api/v1/leave-requests/{$lrId}/approve", ['comment' => 'manager ok', 'idempotency_key' => 'mgr-1']);
        $approveResp2->assertStatus(200);

        $this->assertDatabaseHas('leave_requests', ['id' => $lrId, 'status' => 'approved']);
        $this->employee->refresh();
        $this->assertEquals(7.0, (float)$this->employee->leave_balance); // 10 - 3 = 7
    }

    public function test_manager_cannot_approve_non_subordinate()
    {
        $otherManager = Employee::factory()->create(['role' => 'manager']);
        $otherUserMgr = User::factory()->create();
        $otherUserMgr->employee_id = $otherManager->id;
        $otherUserMgr->save();

        $this->actingAs($this->userEmployee, 'sanctum');
        $start = Carbon::today()->addDays(20)->toDateString();
        $end = Carbon::today()->addDays(20)->toDateString();

        $resp = $this->postJson('/api/v1/leave-requests', [
            'start_date' => $start,
            'end_date' => $end,
            'leave_type' => 'annual',
            'current_stage_id' => $this->hrStage->id,
        ]);
        $lrId = $resp->json('data.id');


        $this->actingAs($this->userHr, 'sanctum');
        $this->postJson("/api/v1/leave-requests/{$lrId}/approve", ['idempotency_key' => 'hr-2']);


        $this->actingAs($otherUserMgr, 'sanctum');
        $resp = $this->postJson("/api/v1/leave-requests/{$lrId}/approve", ['idempotency_key' => 'other-mgr']);
        $resp->assertStatus(422);
    }

    public function test_approve_idempotency_key_prevents_double_effect()
    {
        $this->actingAs($this->userEmployee, 'sanctum');

        $start = Carbon::today()->addDays(30)->toDateString();
        $end = Carbon::today()->addDays(30)->toDateString();

        $resp = $this->postJson('/api/v1/leave-requests', [
            'start_date' => $start,
            'end_date' => $end,
            'leave_type' => 'annual',
            'current_stage_id' => $this->hrStage->id,
        ]);
        $lrId = $resp->json('data.id');


        $this->actingAs($this->userHr, 'sanctum');
        $key = 'idem-key-xyz';
        $r1 = $this->postJson("/api/v1/leave-requests/{$lrId}/approve", ['idempotency_key' => $key]);
        $r1->assertStatus(200);


        $r2 = $this->postJson("/api/v1/leave-requests/{$lrId}/approve", ['idempotency_key' => $key]);
        $r2->assertStatus(200);


        $logs = \DB::table('leave_logs')->get()->map(fn($r) => (array) $r);
        $idemCount = 0;
        foreach ($logs as $l) {
            $meta = $l['meta'] ?? $l->meta ?? null;
            if ($meta) {
                if (is_string($meta)) {
                    $decoded = json_decode($meta, true);
                } else {
                    $decoded = (array) $meta;
                }
                if (! empty($decoded['idempotency_key']) && $decoded['idempotency_key'] === $key) {
                    $idemCount++;
                }
            }
        }
        $this->assertEquals(1, $idemCount);



        $this->employee->refresh();
        $this->assertTrue($this->employee->leave_balance <= 10.0);
    }
}
