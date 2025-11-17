<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee')?->id ?? null;

        return [
            'full_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('employees', 'email')->ignore($employeeId),
            ],
            'position' => 'nullable|string|max:255',
            'manager_id' => 'nullable|integer|exists:employees,id',
            'role' => 'required|string|in:employee,manager,hr,ceo',
            'leave_balance' => 'required|numeric|min:0',
        ];
    }
}
