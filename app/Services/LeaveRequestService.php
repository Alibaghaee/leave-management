<?php

namespace App\Services;

use App\Repositories\LeaveRequestRepositoryInterface;
use App\Models\LeaveRequest;
use App\Models\Stage;
use App\Models\LeaveLog;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaveRequestService
{
    public function __construct(protected LeaveRequestRepositoryInterface $repository)
    {
    }


    public function create(array $data): LeaveRequest
    {
        $employee = Employee::find($data['employee_id'] ?? null);
        if (!$employee) {
            throw ValidationException::withMessages(['employee_id' => 'employee not found']);
        }


        try {
            $start = Carbon::createFromFormat('Y-m-d', $data['start_date'])->startOfDay();
            $end = Carbon::createFromFormat('Y-m-d', $data['end_date'])->startOfDay();
        } catch (\Throwable $e) {
            try {
                $start = Carbon::parse($data['start_date'])->startOfDay();
                $end = Carbon::parse($data['end_date'])->startOfDay();
            } catch (\Throwable) {
                throw ValidationException::withMessages(['date' => 'invalid date format']);
            }
        }


        if ($end->lt($start)) {
            [$start, $end] = [$end, $start];
        }


        $timeStart = $data['start_time'] ?? $data['time_start'] ?? null;
        $timeEnd = $data['end_time'] ?? $data['time_end'] ?? null;


        if ($timeStart !== null || $timeEnd !== null) {

            if (empty($timeStart) || empty($timeEnd)) {
                throw ValidationException::withMessages(['time' => 'invalid time range']);
            }


            $startTimeObj = null;
            $endTimeObj = null;
            $timeFormats = ['H:i', 'H:i:s'];

            foreach ($timeFormats as $fmt) {
                if ($startTimeObj === null) {
                    try {
                        $startTimeObj = Carbon::createFromFormat($fmt, $timeStart);
                    } catch (\Throwable) {
                        $startTimeObj = null;
                    }
                }
                if ($endTimeObj === null) {
                    try {
                        $endTimeObj = Carbon::createFromFormat($fmt, $timeEnd);
                    } catch (\Throwable) {
                        $endTimeObj = null;
                    }
                }
                if ($startTimeObj && $endTimeObj) break;
            }


            if (!$startTimeObj) {
                try {
                    $startTimeObj = Carbon::parse($timeStart);
                } catch (\Throwable) {
                    throw ValidationException::withMessages(['time' => 'invalid time format']);
                }
            }
            if (!$endTimeObj) {
                try {
                    $endTimeObj = Carbon::parse($timeEnd);
                } catch (\Throwable) {
                    throw ValidationException::withMessages(['time' => 'invalid time format']);
                }
            }


            if ($start->toDateString() === $end->toDateString()) {

                $startMinutes = $startTimeObj->hour * 60 + $startTimeObj->minute + (int)floor($startTimeObj->second / 60);
                $endMinutes = $endTimeObj->hour * 60 + $endTimeObj->minute + (int)floor($endTimeObj->second / 60);

                $minutes = $endMinutes - $startMinutes;


                if ($minutes <= 0) {
                    throw ValidationException::withMessages(['time' => 'invalid time range']);
                }

                $hours = $minutes / 60.0;
                $daysCount = round($hours / 8.0, 2);
                if ($daysCount <= 0) {
                    throw ValidationException::withMessages(['time' => 'invalid time range']);
                }
            } else {

                try {
                    $startDateTime = Carbon::parse($start->toDateString() . ' ' . $timeStart);
                    $endDateTime = Carbon::parse($end->toDateString() . ' ' . $timeEnd);
                } catch (\Throwable) {
                    throw ValidationException::withMessages(['time' => 'invalid time format']);
                }


                if ($endDateTime->lte($startDateTime)) {
                    $attempts = 0;
                    while ($endDateTime->lte($startDateTime) && $attempts < 7) {
                        $endDateTime->addDay();
                        $attempts++;
                    }
                }

                if ($endDateTime->lte($startDateTime)) {
                    throw ValidationException::withMessages(['time' => 'invalid time range']);
                }

                $minutes = $endDateTime->diffInMinutes($startDateTime);
                if ($minutes <= 0) {
                    throw ValidationException::withMessages(['time' => 'invalid time range']);
                }

                $hours = $minutes / 60.0;
                $daysCount = round($hours / 8.0, 2);
                if ($daysCount <= 0) {
                    throw ValidationException::withMessages(['time' => 'invalid time range']);
                }
            }

        } else {

            $diffDays = $start->diffInDays($end);
            $daysCount = $diffDays + 1;
            if ($daysCount <= 0) {
                throw ValidationException::withMessages(['date' => 'invalid date range']);
            }
        }


        $overlap = LeaveRequest::where('employee_id', $employee->id)
            ->whereIn('status', ['draft', 'pending_hr', 'pending_manager', 'pending_ceo', 'approved'])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start->toDateString(), $end->toDateString()])
                    ->orWhereBetween('end_date', [$start->toDateString(), $end->toDateString()])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('start_date', '<=', $start->toDateString())
                            ->where('end_date', '>=', $end->toDateString());
                    });
            })->exists();

        if ($overlap) {
            throw ValidationException::withMessages(['date' => 'overlapping leave exists']);
        }


        if (($data['leave_type'] ?? 'annual') === 'annual') {
            if ($employee->leave_balance < $daysCount) {
                throw ValidationException::withMessages(['leave_balance' => 'not enough leave balance']);
            }
        }


        $initialStageId = $data['current_stage_id'] ?? $data['stage_id'] ?? null;
        $status = $data['status'] ?? 'draft';
        if (empty($initialStageId)) {
            $firstStage = Stage::orderBy('order')->first();
            if ($firstStage) {
                $initialStageId = $firstStage->id;
                $statusMap = ['hr' => 'pending_hr', 'manager' => 'pending_manager', 'ceo' => 'pending_ceo'];
                $status = $statusMap[$firstStage->role] ?? 'pending';
            }
        }

        $payload = [
            'employee_id' => $employee->id,
            'leave_type' => $data['leave_type'] ?? 'annual',
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'start_time' => $timeStart,
            'end_time' => $timeEnd,
            'reason' => $data['reason'] ?? null,
            'status' => $status,
            'current_stage_id' => $initialStageId,
            'days_count' => $daysCount,
            'meta' => isset($data['meta']) ? (is_array($data['meta']) ? json_encode($data['meta']) : $data['meta']) : null,
        ];

        return DB::transaction(function () use ($payload, $employee) {
            $lr = $this->repository->create($payload);

            // record creation
            LeaveLog::create([
                'leave_request_id' => $lr->id,
                'action' => 'created',
                'performed_by' => $employee->id,
                'meta' => ['days' => $lr->days_count],
            ]);

            return $lr->fresh();
        });
    }

    public function all(array $filters = [], int $perPage = 10)
    {
        return $this->repository->all($filters, $perPage);
    }

    public function find(int $id): ?LeaveRequest
    {
        return $this->repository->find($id);
    }

    public function update(LeaveRequest $leaveRequest, array $data): LeaveRequest
    {
        $fillable = [
            'start_date', 'end_date', 'start_time', 'end_time', 'reason', 'current_stage_id', 'leave_type', 'meta'
        ];
        foreach ($fillable as $f) {
            if (array_key_exists($f, $data)) {
                $leaveRequest->{$f} = $data[$f];
            }
        }
        if (isset($data['days_count'])) {
            $leaveRequest->days_count = (float)$data['days_count'];
        }
        $leaveRequest->save();

        LeaveLog::create([
            'leave_request_id' => $leaveRequest->id,
            'action' => 'updated',
            'performed_by' => auth()->user()->employee_id ?? auth()->id(),
            'meta' => ['fields' => array_keys($data)],
        ]);

        return $leaveRequest->fresh();
    }

    public function delete(LeaveRequest $leaveRequest): bool
    {
        return $this->repository->delete($leaveRequest);
    }


    public function approve(LeaveRequest $leaveRequest, int $approverId, ?string $comment = null, ?string $idempotencyKey = null)
    {

        if ($idempotencyKey) {
            $existsQuery = LeaveLog::where('leave_request_id', $leaveRequest->id);

            $exists = false;
            try {
                $exists = (clone $existsQuery)
                    ->where('meta->idempotency_key', $idempotencyKey)
                    ->exists();
            } catch (\Throwable $_) {
                $exists = false;
            }

            if (!$exists) {
                try {
                    $exists = (clone $existsQuery)
                        ->whereRaw("json_extract(meta, '$.idempotency_key') = ?", [$idempotencyKey])
                        ->exists();
                } catch (\Throwable $_) {
                    $exists = false;
                }
            }

            if (!$exists) {

                $like = '%"idempotency_key":"' . str_replace('"', '\"', $idempotencyKey) . '"%';
                $exists = (clone $existsQuery)
                    ->where('meta', 'like', $like)
                    ->exists();
            }

            if ($exists) {
                return $leaveRequest->fresh();
            }
        }

        return DB::transaction(function () use ($leaveRequest, $approverId, $comment, $idempotencyKey) {
            $stage = $leaveRequest->currentStage;
            if (!$stage) {
                throw ValidationException::withMessages(['stage' => 'no stage to approve']);
            }

            $approver = Employee::find($approverId);
            if (!$approver) {
                throw ValidationException::withMessages(['approver' => 'approver not found']);
            }


            $allowed = false;
            if (in_array($approver->role, ['hr', 'ceo'])) {
                $allowed = true;
            } elseif ($approver->role === $stage->role) {
                if ($approver->role === 'manager') {
                    if ($leaveRequest->employee && $leaveRequest->employee->manager_id === $approver->id) {
                        $allowed = true;
                    }
                } else {
                    $allowed = true;
                }
            }

            if (!$allowed) {

                if ($approver->role === 'manager' && $stage->role === 'manager') {
                    throw ValidationException::withMessages(['authorize' => 'unauthorized']);
                }


                throw new \Illuminate\Auth\Access\AuthorizationException('unauthorized');
            }


            LeaveLog::create([
                'leave_request_id' => $leaveRequest->id,
                'action' => 'approved',
                'performed_by' => $approverId,
                'meta' => ['comment' => $comment, 'stage' => $stage->name],
            ]);


            if ($stage->next_stage_id) {
                $next = Stage::find($stage->next_stage_id);
                $minRequired = $next->min_days ?? $next->days_min ?? null;
                if ($next && ($minRequired === null || $leaveRequest->days_count >= $minRequired)) {
                    $statusMap = ['hr' => 'pending_hr', 'manager' => 'pending_manager', 'ceo' => 'pending_ceo'];
                    $leaveRequest->update([
                        'current_stage_id' => $next->id,
                        'status' => $statusMap[$next->role] ?? 'pending',
                        'approver_id' => $approverId,
                    ]);
                    LeaveLog::create([
                        'leave_request_id' => $leaveRequest->id,
                        'action' => 'advanced_stage',
                        'performed_by' => $approverId,
                        'meta' => ['to' => $next->name, 'idempotency_key' => $idempotencyKey],
                    ]);
                    return $leaveRequest->fresh();
                }
            }


            $leaveRequest->update([
                'status' => 'approved',
                'approver_id' => $approverId,
                'current_stage_id' => null,
            ]);

            if ($leaveRequest->leave_type === 'annual') {
                $employee = $leaveRequest->employee;
                if ($employee) {
                    $employee->leave_balance = ((float)$employee->leave_balance) - ((float)$leaveRequest->days_count);
                    $employee->save();
                }
            }

            LeaveLog::create([
                'leave_request_id' => $leaveRequest->id,
                'action' => 'final_approved',
                'performed_by' => $approverId,
                'meta' => ['reduced_days' => $leaveRequest->days_count, 'idempotency_key' => $idempotencyKey],
            ]);

            return $leaveRequest->fresh();
        });
    }


    public function reject(LeaveRequest $leaveRequest, int $approverId, ?string $comment = null, ?string $idempotencyKey = null)
    {

        if ($idempotencyKey) {
            $existsQuery = LeaveLog::where('leave_request_id', $leaveRequest->id);
            $exists = false;
            try {
                $exists = (clone $existsQuery)->where('meta->idempotency_key', $idempotencyKey)->exists();
            } catch (\Throwable $_) {
                $exists = false;
            }
            if (!$exists) {
                try {
                    $exists = (clone $existsQuery)->whereRaw("json_extract(meta, '$.idempotency_key') = ?", [$idempotencyKey])->exists();
                } catch (\Throwable $_) {
                    $exists = false;
                }
            }
            if (!$exists) {
                $like = '%"idempotency_key":"' . str_replace('"', '\"', $idempotencyKey) . '"%';
                $exists = (clone $existsQuery)->where('meta', 'like', $like)->exists();
            }
            if ($exists) {
                return $leaveRequest->fresh();
            }
        }

        $leaveRequest->update([
            'status' => 'rejected',
            'approver_id' => $approverId,
            'rejection_reason' => $comment,
            'current_stage_id' => null,
        ]);

        LeaveLog::create([
            'leave_request_id' => $leaveRequest->id,
            'action' => 'rejected',
            'performed_by' => $approverId,
            'meta' => ['comment' => $comment, 'idempotency_key' => $idempotencyKey],
        ]);

        return $leaveRequest->fresh();
    }
}
