<?php

namespace App\Repositories;

use App\Models\LeaveLog;

interface LeaveLogRepositoryInterface
{
    public function find(int $id): ?LeaveLog;
    public function allByRequest(int $leaveRequestId, int $perPage = 25);
    public function create(array $data): LeaveLog;
}
