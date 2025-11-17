<?php

namespace App\Jobs;

use App\Services\EmployeeLeaveSummaryDailyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AggregateEmployeeLeaveSummaryDaily implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    protected $date;

    public function __construct(string $date = null)
    {
        $this->date = $date ?: now()->toDateString();
    }

    public function handle(EmployeeLeaveSummaryDailyService $service)
    {
        $service->aggregateAndUpsert($this->date);
    }
}
