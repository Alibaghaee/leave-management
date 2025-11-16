<?php

namespace App\Services;

use App\Repositories\EmployeeRepositoryInterface;
use App\Models\Employee;

class EmployeeService
{
    public function __construct(protected EmployeeRepositoryInterface $repository)
    {}


    public function find(int $id): ?Employee
    {
        return $this->repository->find($id);
    }


    public function create(array $data): Employee
    {

        return $this->repository->create($data);
    }

    public function all(array $filters = [], int $perPage = 10)
    {
        return $this->repository->all($filters, $perPage);
    }
    public function update(Employee $employee, array $data): Employee
    {
        return $this->repository->update($employee, $data);
    }
    public function delete(Employee $employee): bool
    {
        return $this->repository->delete($employee);
    }


}
