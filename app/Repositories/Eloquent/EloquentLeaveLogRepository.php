<?php

namespace App\Repositories\Eloquent;

use App\Models\LeaveLog;
use App\Repositories\LeaveLogRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentLeaveLogRepository implements LeaveLogRepositoryInterface
{
    public function find(int $id): ?LeaveLog
    {
        return LeaveLog::find($id);
    }

    public function allByRequest(int $leaveRequestId, int $perPage = 25)
    {
        return LeaveLog::where('leave_request_id', $leaveRequestId)->orderBy('created_at')->paginate($perPage);
    }

    public function create(array $data): LeaveLog
    {
        return LeaveLog::create($data);
    }
}
