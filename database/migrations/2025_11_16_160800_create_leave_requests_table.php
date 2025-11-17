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
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->enum('leave_type', ['annual','sick','unpaid','other','hourly'])->default('annual');
            $table->foreignId('approver_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->text('reason')->nullable();
            $table->enum('status', ['draft','pending_hr','pending_manager','pending_ceo','approved','rejected','due_date'])->default('draft');
            $table->foreignId('current_stage_id')->nullable()->constrained('stages')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->decimal('days_count', 8, 2)->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();

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
