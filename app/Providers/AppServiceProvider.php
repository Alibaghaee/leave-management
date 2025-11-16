<?php

namespace App\Providers;

use App\Repositories\Eloquent\EloquentEmployeeLeaveSummaryDailyRepository;
use App\Repositories\Eloquent\EloquentEmployeeRepository;
use App\Repositories\Eloquent\EloquentLeaveLogRepository;
use App\Repositories\Eloquent\EloquentLeaveRequestRepository;
use App\Repositories\Eloquent\EloquentStageRepository;
use App\Repositories\EmployeeLeaveSummaryDailyRepositoryInterface;
use App\Repositories\EmployeeRepositoryInterface;
use App\Repositories\LeaveLogRepositoryInterface;
use App\Repositories\LeaveRequestRepositoryInterface;
use App\Repositories\StageRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(EmployeeRepositoryInterface::class, EloquentEmployeeRepository::class);
        $this->app->bind(LeaveRequestRepositoryInterface::class, EloquentLeaveRequestRepository::class);
        $this->app->bind(StageRepositoryInterface::class, EloquentStageRepository::class);
        $this->app->bind(LeaveLogRepositoryInterface::class, EloquentLeaveLogRepository::class);
        $this->app->bind(EmployeeLeaveSummaryDailyRepositoryInterface::class, EloquentEmployeeLeaveSummaryDailyRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
