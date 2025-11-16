<?php

namespace App\Repositories;

use App\Models\Employee;

interface EmployeeRepositoryInterface
{
    public function find(int $id): ?Employee;
    public function findByEmail(string $email): ?Employee;
    public function all(array $filters = [], int $perPage = 15, ?string $cursor = null);
    public function create(array $data): Employee;
    public function update(Employee $employee, array $data): Employee;
    public function delete(Employee $employee): bool;
}
