<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;

class LeaveRequestPolicy
{
    public function view(User $user, LeaveRequest $leave)
    {
        return $user->id === $leave->employee_id || $user->role === 'hr' || $user->role === 'ceo' || $user->id === optional($leave->employee)->manager_id;
    }

    public function update(User $user, LeaveRequest $leave)
    {
        return in_array($leave->status, ['draft','pending_hr','pending_manager','pending_ceo']) && ($user->id === $leave->employee_id || $user->role === 'hr' || $user->role === 'ceo' || $user->id === optional($leave->employee)->manager_id);
    }

    public function delete(User $user, LeaveRequest $leave)
    {
        return $user->role === 'hr' || $user->role === 'ceo';
    }

    public function create(User $user)
    {
        return $user->role !== null;
    }

    public function getUserRole($user)
    {
        if (!empty($user->role)) {
            return $user->role;
        }
        if (!empty($user->employee_id)) {
            $emp = \App\Models\Employee::find($user->employee_id);
            return $emp->role ?? null;
        }
        return null;
    }

    public function approve(User $user, LeaveRequest $leave)
    {
        $role = $this->getUserRole($user);

        if (in_array($role, ['hr','ceo'])) {
            return true;
        }

        if ($role === 'manager') {
            $userEmployeeId = $user->employee_id ?? null;
            return $userEmployeeId && $leave->employee && $leave->employee->manager_id === $userEmployeeId;
        }

        return false;
    }

    public function reject(User $user, LeaveRequest $leave)
    {
        return $this->approve($user, $leave);
    }

}
