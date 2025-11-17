<?php

namespace App\Repositories;

use App\Models\LeaveRequest;

interface LeaveRequestRepositoryInterface
{
    public function find(int $id): ?LeaveRequest;
    public function all(array $filters = [], int $perPage = 15, ?string $cursor = null);
    public function create(array $data): LeaveRequest;
    public function update(LeaveRequest $leaveRequest, array $data): LeaveRequest;
    public function delete(LeaveRequest $leaveRequest): bool;
}
