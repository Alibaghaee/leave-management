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

        return $this->repository->create($data);
    }
    public function all(): iterable
    {

        return [];
    }
    public function allByRequest(int $leaveRequestId): iterable
    {
        return $this->repository->allByRequest($leaveRequestId);
    }
    public function find($id)
    {
        return $this->repository->find($id);
    }

}
