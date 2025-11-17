<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use App\Services\EmployeeService;
use App\Http\DTOs\Employee\EmployeeCreateDto;
use App\Http\Resources\EmployeeResource;
use App\Http\Requests\EmployeeStoreRequest;

class EmployeeController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected EmployeeService $service) {}

    public function index(Request $request)
    {
        $filters = $request->only(['role','full_name','email','manager_id','has_pending_leaves']);
        $perPage = (int) $request->input('per_page', 10);
        $employees = $this->service->all($filters, $perPage);
        return EmployeeResource::collection($employees);
    }

    public function store(EmployeeStoreRequest $request)
    {
        $this->authorize('create', Employee::class);
        $dto = new EmployeeCreateDto($request->validated());
        $employee = $this->service->create((array) $dto);
        return new EmployeeResource($employee);
    }

    public function show(Employee $employee)
    {
        $this->authorize('view', $employee);
        return new EmployeeResource($employee);
    }

    public function update(EmployeeStoreRequest $request, Employee $employee)
    {
        $this->authorize('update', $employee);
        $dto = new EmployeeCreateDto($request->validated());
        $employee = $this->service->update($employee, (array)$dto);
        return new EmployeeResource($employee);
    }

    public function destroy(Employee $employee)
    {
        $this->authorize('delete', $employee);
        $this->service->delete($employee);
        return response()->noContent();
    }
}
