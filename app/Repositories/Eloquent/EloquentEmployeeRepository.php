<?php

namespace App\Repositories\Eloquent;

use App\Models\Employee;
use App\Repositories\EmployeeRepositoryInterface;

class EloquentEmployeeRepository implements EmployeeRepositoryInterface
{
    public function find(int $id): ?Employee
    {
        return Employee::find($id);
    }

    public function findByEmail(string $email): ?Employee
    {
        return Employee::where('email', $email)->first();
    }

    public function all(array $filters = [], int $perPage = 15, ?string $cursor = null)
    {
        $query = Employee::query();


        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }
        if (!empty($filters['name_full'])) {
            $query->where('name_full', 'like', "%{$filters['name_full']}%");
        }
        if (!empty($filters['email'])) {
            $query->where('email', $filters['email']);
        }
        if (!empty($filters['manager_id'])) {
            $query->where('manager_id', $filters['manager_id']);
        }

        // TODO: filter for has_pending_leaves /// subquery or join with leave_requests


        if ($cursor) {
            $query->where('id', '>', $cursor);
        }
        return $query->orderBy('id')->paginate($perPage);
    }

    public function create(array $data): Employee
    {
        return Employee::create($data);
    }

    public function update(Employee $employee, array $data): Employee
    {
        $employee->update($data);
        return $employee;
    }

    public function delete(Employee $employee): bool
    {
        return $employee->delete();
    }
}
