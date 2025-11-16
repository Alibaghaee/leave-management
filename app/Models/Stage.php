<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stage extends Model
{
    protected $fillable = [
        'name',
        'role',
        'order',
        'days_min',
        'next_stage_id',
    ];

    public function nextStage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'next_stage_id');
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'current_stage_id');
    }
}
