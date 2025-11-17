<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EmployeeLeaveSummaryDailyService;
use App\Http\Resources\EmployeeLeaveSummaryDailyResource;

class EmployeeLeaveSummaryDailyController extends Controller
{
    protected $service;

    public function __construct(EmployeeLeaveSummaryDailyService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['employee_id', 'date', 'leave_type']);
        $perPage = (int) $request->input('per_page', 50);
        $results = $this->service->filter($filters, $perPage);
        return EmployeeLeaveSummaryDailyResource::collection($results);
    }

    public function aggregate(Request $request)
    {
        $request->validate(['date' => 'required|date']);

        $user = $request->user();
        $role = $user->role ?? null;
        if (!$role && !empty($user->employee_id)) {
            $emp = \App\Models\Employee::find($user->employee_id);
            $role = $emp->role ?? null;
        }

        if (! in_array($role, ['hr','ceo'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->service->aggregateAndUpsert($request->input('date'));
        return response()->json(['status' => 'aggregated'], 200);
    }
}
