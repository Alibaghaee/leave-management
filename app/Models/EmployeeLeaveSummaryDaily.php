<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeLeaveSummaryDaily extends Model
{


    protected $fillable = [
        'employee_id',
        'date',
        'leave_type',
        'requested_days',
        'approved_days',
        'rejected_days',
        'pending_days',
        'leave_count',
    ];
}
