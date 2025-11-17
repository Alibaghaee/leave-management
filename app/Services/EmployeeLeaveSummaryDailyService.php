<?php

namespace App\Services;

use App\Repositories\EmployeeLeaveSummaryDailyRepositoryInterface;
use Illuminate\Support\Facades\DB;

class EmployeeLeaveSummaryDailyService
{
    protected $repo;

    public function __construct(EmployeeLeaveSummaryDailyRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function aggregateAndUpsert(string $date): void
    {
        $rows = DB::table('leave_requests')
            ->select(
                'employee_id',
                DB::raw('DATE(start_date) as date'),
                'leave_type',
                DB::raw('SUM(CASE WHEN status IN ("pending_hr","pending_manager","pending_ceo") THEN days_count ELSE 0 END) AS pending_days'),
                DB::raw('SUM(CASE WHEN status = "approved" THEN days_count ELSE 0 END) AS approved_days'),
                DB::raw('SUM(CASE WHEN status = "rejected" THEN days_count ELSE 0 END) AS rejected_days'),
                DB::raw('SUM(days_count) AS requested_days'),
                DB::raw('COUNT(*) AS leave_count')
            )
            ->whereDate('start_date', $date)
            ->groupBy('employee_id', 'date', 'leave_type')
            ->get();

        $records = [];
        foreach ($rows as $r) {
            $records[] = [
                'employee_id' => $r->employee_id,
                'date' => $r->date,
                'leave_type' => $r->leave_type,
                'pending_days' => $r->pending_days,
                'approved_days' => $r->approved_days,
                'rejected_days' => $r->rejected_days,
                'requested_days' => $r->requested_days,
                'leave_count' => $r->leave_count,
            ];
        }
        if (! empty($records)) {
            $this->repo->upsertMany($records);
        }
    }

    public function filter(array $filters = [], int $perPage = 50)
    {
        return $this->repo->filter($filters, $perPage);
    }
}
