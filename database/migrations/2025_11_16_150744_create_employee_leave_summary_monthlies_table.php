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
        Schema::create('employee_leave_summary_monthlies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->year('year');
            $table->unsignedTinyInteger('month');
            $table->string('leave_type');
            $table->decimal('requested_days', 10, 2)->default(0);
            $table->decimal('approved_days', 10, 2)->default(0);
            $table->decimal('rejected_days', 10, 2)->default(0);
            $table->decimal('pending_days', 10, 2)->default(0);
            $table->unsignedInteger('leave_count')->default(0);
            $table->timestamps();
            $table->unique(['employee_id','year','month','leave_type'],'unique_summary_monthly');
            $table->index('employee_id');
            $table->index(['year','month']);
            $table->index('leave_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_leave_summary_monthlies');
    }
};
