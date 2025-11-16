<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->enum('leave_type', ['annual', 'sick', 'unpaid', 'other']);
            $table->unsignedBigInteger('approver_id')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->time('time_start')->nullable();
            $table->time('time_end')->nullable();
            $table->text('reason');
            $table->enum('status', ['draft','pending_hr','pending_manager','pending_ceo','approved','rejected','due_date']);
            $table->unsignedBigInteger('current_stage_id');
            $table->text('rejection_reason')->nullable();
            $table->integer('count_days');
            $table->timestamps();
            $table->foreign('employee_id')->references('id')->on('employees');
            $table->foreign('approver_id')->references('id')->on('employees');
            $table->foreign('current_stage_id')->references('id')->on('stages');
            $table->index('employee_id');
            $table->index('status');
            $table->index('current_stage_id');
            $table->index('start_date');
            $table->index('leave_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
