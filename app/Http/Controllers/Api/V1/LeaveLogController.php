<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use App\Services\LeaveLogService;
use App\Http\Resources\LeaveLogResource;
use App\Http\Requests\LeaveLogStoreRequest;
use App\Models\LeaveLog;

class LeaveLogController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected LeaveLogService $service) {}

    public function index(Request $request)
    {
        $leaveRequestId = $request->input('leave_request_id');
        $perPage = (int) $request->input('per_page', 25);
        $logs = $leaveRequestId
            ? $this->service->allByRequest((int) $leaveRequestId, $perPage)
            : $this->service->all($perPage);
        return LeaveLogResource::collection($logs);
    }

    public function store(LeaveLogStoreRequest $request)
    {
        $this->authorize('create', LeaveLog::class);
        $data = $request->validated();
        $user = $request->user();
        $data['performed_by'] = $user->employee_id ?? $user->id;
        $log = $this->service->create($data);
        return new LeaveLogResource($log);
    }

    public function show(LeaveLog $leaveLog)
    {
        $this->authorize('view', $leaveLog);
        return new LeaveLogResource($leaveLog);
    }
}
