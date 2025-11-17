<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LeaveRequestApproveRequest extends FormRequest
{
    public function authorize(): bool
    {

        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'comment' => 'nullable|string|max:500',
            'idempotency_key' => 'nullable|string|max:255',
        ];
    }
}
