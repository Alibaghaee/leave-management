<?php

namespace App\Repositories\Eloquent;

use App\Models\LeaveLog;
use App\Repositories\LeaveLogRepositoryInterface;

class EloquentLeaveLogRepository implements LeaveLogRepositoryInterface
{
    public function find(int $id): ?LeaveLog
    {
        return LeaveLog::find($id);
    }
    public function allByRequest(int $leaveRequestId): iterable
    {
        return LeaveLog::where('leave_request_id', $leaveRequestId)->get();
    }
    public function create(array $data): LeaveLog
    {
        return LeaveLog::create($data);
    }
}
