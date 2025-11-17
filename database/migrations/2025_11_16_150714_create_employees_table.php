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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('position')->nullable();
            $table->foreignId('manager_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->enum('role', ['employee','manager','hr','ceo'])->default('employee');
            $table->decimal('leave_balance', 8, 2)->default(0);
            $table->date('join_date')->nullable();
            $table->timestamps();
            $table->index('manager_id');
            $table->index('role');
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
