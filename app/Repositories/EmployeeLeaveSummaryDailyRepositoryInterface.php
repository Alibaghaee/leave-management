<?php

namespace App\Repositories;

use App\Models\EmployeeLeaveSummaryDaily;

interface EmployeeLeaveSummaryDailyRepositoryInterface
{
    public function upsertMany(array $records): void;
    public function findByEmployeeAndDate(int $employeeId, string $date, string $leaveType): ?EmployeeLeaveSummaryDaily;
    public function filter(array $filters = [], int $perPage = 50);
}
