<?php

namespace App\Services;

use App\Repositories\LeaveLogRepositoryInterface;
use App\Models\LeaveLog;

class LeaveLogService
{
    public function __construct(protected LeaveLogRepositoryInterface $repository)
    {}

    public function create(array $data): LeaveLog
    {
        $payload = [
            'leave_request_id' => $data['leave_request_id'],
            'action' => $data['action'],
            'performed_by' => $data['performed_by'] ?? null,
            'meta' => isset($data['meta']) ? json_encode($data['meta']) : (isset($data['comment']) ? json_encode(['comment' => $data['comment']]) : null),
        ];
        return $this->repository->create($payload);
    }

    public function all(int $perPage = 25)
    {
        return $this->repository->all($perPage);
    }

    public function allByRequest(int $leaveRequestId, int $perPage = 25)
    {
        return $this->repository->allByRequest($leaveRequestId, $perPage);
    }

    public function find(int $id)
    {
        return $this->repository->find($id);
    }
}
