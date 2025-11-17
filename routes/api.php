<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\EmployeeController;
use App\Http\Controllers\Api\V1\LeaveRequestController;
use App\Http\Controllers\Api\V1\StageController;
use App\Http\Controllers\Api\V1\LeaveLogController;
use App\Http\Controllers\Api\V1\EmployeeLeaveSummaryDailyController;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    Route::apiResource('employees', EmployeeController::class);
    Route::apiResource('leave-requests', LeaveRequestController::class);
    Route::apiResource('stages', StageController::class);
    Route::apiResource('leave-logs', LeaveLogController::class)->only(['index', 'show', 'store']);
    Route::post('leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve']);
    Route::post('leave-requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject']);
    Route::get('employee-leave-summaries/daily', [EmployeeLeaveSummaryDailyController::class, 'index']);
    Route::post('employee-leave-summaries/daily/aggregate', [EmployeeLeaveSummaryDailyController::class, 'aggregate']);

});
