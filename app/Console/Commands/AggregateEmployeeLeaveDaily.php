<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\AggregateEmployeeLeaveSummaryDaily;

class AggregateEmployeeLeaveDaily extends Command
{
    protected $signature = 'leave:aggregate-employee-leave-daily {--date= : Date for aggregation (Y-m-d)}';
    protected $description = 'Aggregate and upsert daily employee leave summaries (materialize report)';

    public function handle()
    {
        $date = $this->option('date') ?: now()->toDateString();
        AggregateEmployeeLeaveSummaryDaily::dispatch($date);
        $this->info("Dispatched aggregation job for date: {$date}");
    }
}
