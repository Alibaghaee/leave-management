<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('role', ['hr','manager','ceo']);
            $table->unsignedInteger('order')->unique();
            $table->unsignedInteger('min_days')->nullable();
            $table->foreignId('next_stage_id')->nullable()->constrained('stages')->nullOnDelete();
            $table->timestamps();
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stages');
    }
};
