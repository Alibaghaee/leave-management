<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeLeaveSummaryDailyResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'employee_id' => $this->employee_id,
            'date' => $this->date,
            'leave_type' => $this->leave_type,
            'requested_days' => (float)$this->requested_days,
            'approved_days' => (float)$this->approved_days,
            'rejected_days' => (float)$this->rejected_days,
            'pending_days' => (float)$this->pending_days,
            'leave_count' => (int)$this->leave_count,
        ];
    }
}
