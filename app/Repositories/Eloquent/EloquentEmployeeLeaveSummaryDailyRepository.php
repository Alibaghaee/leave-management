<?php

namespace App\Repositories\Eloquent;

use App\Models\EmployeeLeaveSummaryDaily;
use App\Repositories\EmployeeLeaveSummaryDailyRepositoryInterface;

class EloquentEmployeeLeaveSummaryDailyRepository implements EmployeeLeaveSummaryDailyRepositoryInterface
{
    public function upsertMany(array $records): void
    {
        EmployeeLeaveSummaryDaily::upsert($records, ['employee_id', 'date', 'leave_type']);
    }

    public function findByEmployeeAndDate(int $employeeId, string $date, string $leaveType): ?EmployeeLeaveSummaryDaily
    {
        return EmployeeLeaveSummaryDaily::where('employee_id', $employeeId)
            ->where('date', $date)
            ->where('leave_type', $leaveType)
            ->first();
    }

    public function filter(array $filters = [], int $perPage = 50)
    {
        $query = EmployeeLeaveSummaryDaily::query();
        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }
        if (!empty($filters['date'])) {
            $query->where('date', $filters['date']);
        }
        if (!empty($filters['leave_type'])) {
            $query->where('leave_type', $filters['leave_type']);
        }

        return $query->orderByDesc('date')->paginate($perPage);
    }
}
