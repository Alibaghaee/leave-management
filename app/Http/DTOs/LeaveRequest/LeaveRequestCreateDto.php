<?php

namespace LeaveRequest;


class LeaveRequestCreateDto
{
    public int $employee_id;
    public string $leave_type;
    public string $start_date;
    public string $end_date;
    public ?string $time_start;
    public ?string $time_end;
    public string $reason;
    public ?int $approver_id;
    public string $status;
    public int $current_stage_id;
    public ?string $rejection_reason;
    public int $count_days;

    public function __construct(array $data)
    {
        $this->employee_id = $data['employee_id'] ?? 0;
        $this->leave_type = $data['leave_type'] ?? 'annual';
        $this->start_date = $data['start_date'] ?? '';
        $this->end_date = $data['end_date'] ?? '';
        $this->time_start = $data['time_start'] ?? null;
        $this->time_end = $data['time_end'] ?? null;
        $this->reason = $data['reason'] ?? '';
        $this->approver_id = $data['approver_id'] ?? null;
        $this->status = $data['status'] ?? 'draft';
        $this->current_stage_id = $data['current_stage_id'] ?? 0;
        $this->rejection_reason = $data['rejection_reason'] ?? null;
        $this->count_days = $data['count_days'] ?? 1;
    }
}
