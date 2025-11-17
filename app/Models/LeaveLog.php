<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveLog extends Model
{
    protected $fillable = [
        'leave_request_id',
        'action',
        'performed_by',
        'meta',
    ];
    protected $casts = ['meta' => 'array'];

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class, 'leave_request_id');
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'performed_by');
    }
}
