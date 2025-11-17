<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LeaveLogResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'leave_request_id' => $this->leave_request_id,
            'action' => $this->action,
            'performed_by' => $this->performed_by,
            'meta' => $this->meta,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
