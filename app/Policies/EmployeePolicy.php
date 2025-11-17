<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    public function view(User $user, Employee $employee): bool
    {
        $userEmployeeId = $user->employee_id ?? $user->id;
        if ($user->role === 'hr' || $user->role === 'ceo') {
            return true;
        }
        if ($userEmployeeId === $employee->id) {
            return true;
        }
        if ($employee->manager_id && $userEmployeeId === $employee->manager_id) {
            return true;
        }
        return false;
    }

    public function update(User $user, Employee $employee): bool
    {
        $userEmployeeId = $user->employee_id ?? $user->id;
        if ($user->role === 'hr' || $user->role === 'ceo') {
            return true;
        }
        if ($userEmployeeId === $employee->id) {
            return true;
        }
        if ($employee->manager_id && $userEmployeeId === $employee->manager_id) {
            return true;
        }
        return false;
    }

    public function delete(User $user, Employee $employee): bool
    {
        return in_array($user->role, ['hr','ceo']);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['hr','ceo']);
    }
}
