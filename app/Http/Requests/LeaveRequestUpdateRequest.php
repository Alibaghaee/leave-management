<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LeaveRequestUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'sometimes|integer|exists:employees,id',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'start_time' => 'sometimes|nullable|date_format:H:i',
            'end_time' => 'sometimes|nullable|date_format:H:i|required_with:start_time',
            'reason' => 'sometimes|string|min:3',
            'current_stage_id' => 'sometimes|integer|exists:stages,id',
            'days_count' => 'sometimes|numeric|min:0.01',
            'leave_type' => 'sometimes|string|in:annual,sick,unpaid,hourly,other',
            'meta' => 'nullable|array',
        ];
    }
}
