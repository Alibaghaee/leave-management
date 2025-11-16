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
        Schema::create('leave_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('leave_request_id');
            $table->string('action');
            $table->unsignedBigInteger('performed_by');
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->foreign('leave_request_id')->references('id')->on('leave_requests');
            $table->foreign('performed_by')->references('id')->on('employees');
            $table->index('leave_request_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_logs');
    }
};
