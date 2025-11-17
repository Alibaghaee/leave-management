<?php

namespace App\Services;

use App\Repositories\StageRepositoryInterface;
use App\Models\Stage;

class StageService
{
    public function __construct(protected StageRepositoryInterface $repository)
    {}

    public function all(): iterable
    {
        return $this->repository->all();
    }

    public function find(int $id): ?Stage
    {
        return $this->repository->find($id);
    }

    public function create(array $data): Stage
    {
        return $this->repository->create($data);
    }

    public function update(Stage $stage, array $data): Stage
    {
        return $this->repository->update($stage, $data);
    }

    public function delete(Stage $stage): bool
    {
        return $this->repository->delete($stage);
    }
}
