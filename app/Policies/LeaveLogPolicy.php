<?php

namespace App\Policies;

use App\Models\LeaveLog;
use App\Models\User;

class LeaveLogPolicy
{
    public function view(User $user, LeaveLog $log): bool
    {
        $userEmployeeId = $user->employee_id ?? $user->id;
        if (in_array($user->role, ['hr','ceo'])) {
            return true;
        }
        if ($log->performed_by && $log->performed_by === $userEmployeeId) {
            return true;
        }
        if ($log->leaveRequest && $log->leaveRequest->employee_id === $userEmployeeId) {
            return true;
        }
        return false;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['hr','ceo','manager','employee']);
    }

    public function update(User $user, LeaveLog $log): bool
    {
        return in_array($user->role, ['hr','ceo']);
    }

    public function delete(User $user, LeaveLog $log): bool
    {
        return in_array($user->role, ['hr','ceo']);
    }
}
