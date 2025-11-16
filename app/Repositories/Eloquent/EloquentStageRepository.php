<?php

namespace App\Repositories\Eloquent;

use App\Models\Stage;
use App\Repositories\StageRepositoryInterface;

class EloquentStageRepository implements StageRepositoryInterface
{
    public function find(int $id): ?Stage
    {
        return Stage::find($id);
    }
    public function all(): iterable
    {
        return Stage::all();
    }
    public function create(array $data): Stage
    {
        return Stage::create($data);
    }
    public function update(Stage $stage, array $data): Stage
    {
        $stage->update($data);
        return $stage;
    }
    public function delete(Stage $stage): bool
    {
        return $stage->delete();
    }
}
