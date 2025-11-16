<?php

namespace App\Repositories;

use App\Models\Stage;

interface StageRepositoryInterface
{
    public function find(int $id): ?Stage;
    public function all(): iterable;
    public function create(array $data): Stage;
    public function update(Stage $stage, array $data): Stage;
    public function delete(Stage $stage): bool;
}
