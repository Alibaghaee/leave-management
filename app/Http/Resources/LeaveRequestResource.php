<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'approver_id' => $this->approver_id,
            'leave_type' => $this->leave_type ?? null,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'time_start' => $this->time_start,
            'time_end' => $this->time_end,
            'reason' => $this->reason,
            'status' => $this->status,
            'current_stage_id' => $this->current_stage_id,
            'rejection_reason' => $this->rejection_reason,
            'count_days' => $this->count_days,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
