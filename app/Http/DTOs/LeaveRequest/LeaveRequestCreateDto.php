<?php

namespace App\Http\DTOs\LeaveRequest;

class LeaveRequestCreateDto
{
    public int $employee_id;
    public string $leave_type;
    public string $start_date;
    public string $end_date;
    public ?string $start_time;
    public ?string $end_time;
    public ?string $reason;
    public ?int $approver_id;
    public string $status;
    public ?int $current_stage_id;
    public ?string $rejection_reason;
    public float $days_count;
    public ?array $meta;

    public function __construct(array $data)
    {
        $this->employee_id = $data['employee_id'] ?? 0;
        $this->leave_type = $data['leave_type'] ?? 'annual';
        $this->start_date = $data['start_date'] ?? '';
        $this->end_date = $data['end_date'] ?? '';
        $this->start_time = $data['start_time'] ?? null;
        $this->end_time = $data['end_time'] ?? null;
        $this->reason = $data['reason'] ?? null;
        $this->approver_id = $data['approver_id'] ?? null;
        $this->status = $data['status'] ?? 'draft';
        $this->current_stage_id = $data['current_stage_id'] ?? null;
        $this->rejection_reason = $data['rejection_reason'] ?? null;
        $this->days_count = isset($data['days_count']) ? (float)$data['days_count'] : 0.0;
        $this->meta = $data['meta'] ?? null;
    }
}
