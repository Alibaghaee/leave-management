<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeLeaveSummaryDailyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'employee_id' => $this->employee_id,
            'date' => $this->date,
            'leave_type' => $this->leave_type,
            'requested_days' => $this->requested_days,
            'approved_days' => $this->approved_days,
            'rejected_days' => $this->rejected_days,
            'pending_days' => $this->pending_days,
            'leave_count' => $this->leave_count,
        ];
    }
}
