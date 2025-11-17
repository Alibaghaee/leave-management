<?php

namespace App\Repositories\Eloquent;

use App\Models\Employee;
use App\Repositories\EmployeeRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

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
        if (!empty($filters['full_name'])) {
            $query->where('full_name', 'like', "%{$filters['full_name']}%");
        }
        if (!empty($filters['email'])) {
            $query->where('email', $filters['email']);
        }
        if (!empty($filters['manager_id'])) {
            $query->where('manager_id', $filters['manager_id']);
        }
        if (!empty($filters['has_pending_leaves'])) {
            $query->whereExists(function ($q) {
                $q->selectRaw('1')
                    ->from('leave_requests')
                    ->whereRaw('leave_requests.employee_id = employees.id')
                    ->whereIn('leave_requests.status', ['pending_hr','pending_manager','pending_ceo']);
            });
        }

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
