<?php

namespace App\Repositories\Eloquent;

use App\Models\LeaveRequest;
use App\Models\Employee;
use App\Repositories\LeaveRequestRepositoryInterface;

class EloquentLeaveRequestRepository implements LeaveRequestRepositoryInterface
{
    public function find(int $id): ?LeaveRequest
    {
        return LeaveRequest::find($id);
    }

    public function all(array $filters = [], int $perPage = 15, ?string $cursor = null)
    {
        $query = LeaveRequest::query()->with('employee','stage');

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['current_stage_id'])) {
            $query->where('current_stage_id', $filters['current_stage_id']);
        }
        if (!empty($filters['manager_id'])) {
            $query->whereExists(function ($q) use ($filters) {
                $q->selectRaw('1')
                    ->from('employees')
                    ->whereRaw('employees.id = leave_requests.employee_id')
                    ->where('employees.manager_id', $filters['manager_id']);
            });
        }
        if (!empty($filters['date_from'])) {
            $query->where('start_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->where('end_date', '<=', $filters['date_to']);
        }
        if (!empty($filters['date_range']) && is_array($filters['date_range'])) {
            [$start, $end] = $filters['date_range'];
            $query->whereBetween('start_date', [$start, $end]);
        }
        if ($cursor) {
            $query->where('id', '>', $cursor);
        }
        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    public function create(array $data): LeaveRequest
    {
        return LeaveRequest::create($data);
    }

    public function update(LeaveRequest $leaveRequest, array $data): LeaveRequest
    {
        $leaveRequest->update($data);
        return $leaveRequest;
    }

    public function delete(LeaveRequest $leaveRequest): bool
    {
        return $leaveRequest->delete();
    }
}
