<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LeaveRequestResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'employee_name' => optional($this->employee)->name_full ?? optional($this->employee)->full_name,
            'approver_id' => $this->approver_id,
            'leave_type' => $this->leave_type,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'start_time' => $this->start_time ?? null,
            'end_time' => $this->end_time ?? null,
            'reason' => $this->reason,
            'status' => $this->status,
            'current_stage_id' => $this->current_stage_id ?? null,
            'rejection_reason' => $this->rejection_reason,
            'days_count' => (float)($this->days_count ?? 0),
            'meta' => is_string($this->meta) ? (json_decode($this->meta, true) ?: null) : $this->meta,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
