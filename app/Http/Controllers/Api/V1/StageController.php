<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use App\Services\StageService;
use App\Http\Resources\StageResource;
use App\Http\Requests\StageStoreRequest;
use App\Models\Stage;

class StageController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected StageService $service) {}

    public function index(Request $request)
    {
        $stages = $this->service->all();
        return StageResource::collection($stages);
    }

    public function store(StageStoreRequest $request)
    {
        $this->authorize('create', Stage::class);
        $stage = $this->service->create($request->validated());
        return new StageResource($stage);
    }

    public function show(Stage $stage)
    {
        return new StageResource($stage);
    }

    public function update(StageStoreRequest $request, Stage $stage)
    {
        $this->authorize('update', $stage);
        $data = $request->only(['name','role','order','min_days','next_stage_id']);
        $stage = $this->service->update($stage, $data);
        return new StageResource($stage);
    }

    public function destroy(Stage $stage)
    {
        $this->authorize('delete', $stage);
        $this->service->delete($stage);
        return response()->noContent();
    }
}
