<?php

namespace App\Services;

use App\Repositories\LeaveRequestRepositoryInterface;
use App\Models\LeaveRequest;

class LeaveRequestService
{
    public function __construct(protected LeaveRequestRepositoryInterface $repository)
    {}


    public function create(array $data): LeaveRequest
    {

        return $this->repository->create($data);
    }

    public function all(array $filters = [], int $perPage = 10)
    {
        return $this->repository->all($filters, $perPage);
    }
    public function find(int $id): ?LeaveRequest
    {
        return $this->repository->find($id);
    }
    public function update(LeaveRequest $leaveRequest, array $data): LeaveRequest
    {
        return $this->repository->update($leaveRequest, $data);
    }
    public function delete(LeaveRequest $leaveRequest): bool
    {
        return $this->repository->delete($leaveRequest);
    }
    public function approve(int $leaveRequestId, array $data)
    {

        return $this->repository->find($leaveRequestId);

    }
    public function reject(int $leaveRequestId, array $data)
    {

        return $this->repository->find($leaveRequestId);
    }
}
