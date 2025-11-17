<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveRequest extends Model
{
    protected $fillable = [
        'employee_id',
        'leave_type',
        'approver_id',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'reason',
        'status',
        'current_stage_id',
        'rejection_reason',
        'days_count',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'days_count' => 'float',
        'leave_balance' => 'float',
    ];
    public function getDaysCountAttribute()
    {
        return $this->attributes['days_count'] ?? ($this->attributes['days_count'] ?? 0);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approver_id');
    }

    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'current_stage_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(LeaveLog::class, 'leave_request_id');
    }
}
