<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LeaveLogStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'leave_request_id' => 'required|integer|exists:leave_requests,id',
            'action' => 'required|string|max:255',
            'performed_by' => 'nullable|integer|exists:employees,id',
            'comment' => 'nullable|string|max:2000',
            'meta' => 'nullable|array',
        ];
    }
}
