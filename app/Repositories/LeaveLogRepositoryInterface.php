<?php

namespace App\Repositories;

use App\Models\LeaveLog;

interface LeaveLogRepositoryInterface
{
    public function find(int $id): ?LeaveLog;
    public function allByRequest(int $leaveRequestId): iterable;
    public function create(array $data): LeaveLog;
}
