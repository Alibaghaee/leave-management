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
            $table->string('name_full');
            $table->string('email')->unique();
            $table->string('position');
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->enum('role', ['employee','manager','hr','ceo']);
            $table->integer('leave_balance');
            $table->timestamps();
            $table->index('manager_id');
            $table->index('role');
            $table->index('email');
            $table->foreign('manager_id')->references('id')->on('employees')->nullOnDelete();
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
