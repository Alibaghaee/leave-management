<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'position' => $this->position,
            'manager_id' => $this->manager_id,
            'role' => $this->role,
            'leave_balance' => (float)$this->leave_balance,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
