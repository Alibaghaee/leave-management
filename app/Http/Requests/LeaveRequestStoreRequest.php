<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LeaveRequestStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'nullable|integer|exists:employees,id',
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|required_with:start_time',
            'reason' => 'nullable|string|min:3',
            'current_stage_id' => 'nullable|integer|exists:stages,id',
            'days_count' => 'nullable|numeric|min:0.01',
            'leave_type' => 'required|string|in:annual,sick,unpaid,hourly,other',
            'meta' => 'nullable|array',
        ];
    }
}
