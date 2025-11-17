<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StageStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'role' => 'required|string|in:hr,manager,ceo',
            'order' => 'required|integer|min:1',
            'min_days' => 'nullable|integer|min:0',
            'next_stage_id' => 'nullable|integer|exists:stages,id',
        ];
    }
}
