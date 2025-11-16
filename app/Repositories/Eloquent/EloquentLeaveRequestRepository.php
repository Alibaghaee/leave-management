<?php

namespace App\Repositories\Eloquent;

use App\Models\LeaveRequest;
use App\Repositories\LeaveRequestRepositoryInterface;

class EloquentLeaveRequestRepository implements LeaveRequestRepositoryInterface
{
    public function find(int $id): ?LeaveRequest
    {
        return LeaveRequest::find($id);
    }
    public function all(array $filters = [], int $perPage = 15, ?string $cursor = null)
    {
        $query = LeaveRequest::query();

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['stage'])) {
            $query->where('current_stage_id', $filters['stage']);
        }
        if (!empty($filters['manager_id'])) {
            // join with employees to get manager relation
        }
        if (!empty($filters['date_range'])) {
            [$start, $end] = $filters['date_range'];
            $query->whereBetween('start_date', [$start, $end]);
        }
        if ($cursor) {
            $query->where('id', '>', $cursor);
        }
        return $query->orderBy('id')->paginate($perPage);
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
