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
        Schema::create('employee_leave_summary_yearlies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->year('year');
            $table->string('leave_type');
            $table->integer('requested_days')->default(0);
            $table->integer('approved_days')->default(0);
            $table->integer('rejected_days')->default(0);
            $table->integer('pending_days')->default(0);
            $table->integer('leave_count')->default(0);
            $table->timestamps();
            $table->unique(['employee_id', 'year', 'leave_type'], 'unique_summary_yearly');
            $table->index('employee_id');
            $table->index('year');
            $table->index('leave_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_leave_summary_yearlies');
    }
};
