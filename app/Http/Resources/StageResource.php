<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'role' => $this->role,
            'order' => $this->order,
            'min_days' => $this->min_days,
            'next_stage_id' => $this->next_stage_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
