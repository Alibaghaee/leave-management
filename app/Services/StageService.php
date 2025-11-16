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

    public function find($id)
    {
        return $this->repository->find($id);
    }
    public function create(array $data)
    {
        return $this->repository->create($data);
    }
    public function update($stage, array $data)
    {
        return $this->repository->update($stage, $data);
    }
    public function delete($stage)
    {
        return $this->repository->delete($stage);
    }
}
