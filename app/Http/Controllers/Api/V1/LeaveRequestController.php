<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LeaveRequestApproveRequest;
use App\Http\Requests\LeaveRequestStoreRequest;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use App\Services\LeaveRequestService;
use App\Http\DTOs\LeaveRequest\LeaveRequestCreateDto;
use App\Http\Resources\LeaveRequestResource;

class LeaveRequestController extends Controller
{
    public function __construct(protected LeaveRequestService $service) {}

    public function index(Request $request)
    {
        $filters = $request->only(['employee_id', 'status', 'date_from', 'date_to', 'current_stage_id', 'manager_id']);
        $perPage = (int) $request->input('per_page', 10);
        $requests = $this->service->all($filters, $perPage);
        return LeaveRequestResource::collection($requests);
    }

    public function store(LeaveRequestStoreRequest $request)
    {
        $data = $request->validated();
        $user = $request->user();
        if (empty($data['employee_id'])) {
            $employeeId = $user->employee_id ?? $user->id;
            $data['employee_id'] = $employeeId;
        }
        $dto = new LeaveRequestCreateDto($data);
        $requestEntity = $this->service->create((array)$dto);
        return (new LeaveRequestResource($requestEntity))->response()->setStatusCode(201);
    }

    public function show(LeaveRequest $leaveRequest)
    {
        return new LeaveRequestResource($leaveRequest->load('employee','currentStage','logs'));
    }

    public function update(LeaveRequestStoreRequest $request, LeaveRequest $leaveRequest)
    {
        $dto = new LeaveRequestCreateDto($request->validated());
        $leaveRequest = $this->service->update($leaveRequest, (array)$dto);
        return new LeaveRequestResource($leaveRequest);
    }

    public function destroy(LeaveRequest $leaveRequest)
    {
        $this->service->delete($leaveRequest);
        return response()->noContent();
    }

    public function approve(LeaveRequestApproveRequest $request, LeaveRequest $leaveRequest)
    {
        $comment = $request->input('comment', null);
        $approverId = $request->user()->employee_id ?? $request->user()->id;
        $idempotencyKey = $request->input('idempotency_key', null);
        $leaveRequest = $this->service->approve($leaveRequest, $approverId, $comment, $idempotencyKey);
        return new LeaveRequestResource($leaveRequest);
    }

    public function reject(LeaveRequestApproveRequest $request, LeaveRequest $leaveRequest)
    {
        $comment = $request->input('comment', null);
        $approverId = $request->user()->employee_id ?? $request->user()->id;
        $idempotencyKey = $request->input('idempotency_key', null);
        $leaveRequest = $this->service->reject($leaveRequest, $approverId, $comment, $idempotencyKey);
        return new LeaveRequestResource($leaveRequest);
    }
}
