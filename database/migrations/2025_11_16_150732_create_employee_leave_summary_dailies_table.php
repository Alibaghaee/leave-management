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
        Schema::create('employee_leave_summary_dailies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('date');
            $table->string('leave_type');
            $table->decimal('requested_days', 8, 2)->default(0);
            $table->decimal('approved_days', 8, 2)->default(0);
            $table->decimal('rejected_days', 8, 2)->default(0);
            $table->decimal('pending_days', 8, 2)->default(0);
            $table->unsignedInteger('leave_count')->default(0);
            $table->timestamps();
            $table->unique(['employee_id','date','leave_type'],'unique_summary_daily');
            $table->index('employee_id');
            $table->index('date');
            $table->index('leave_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_leave_summary_dailies');
    }
};
