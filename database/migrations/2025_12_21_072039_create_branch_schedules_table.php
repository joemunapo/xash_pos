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
        Schema::create('branch_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('day_of_week'); // 0 = Sunday, 1 = Monday, ..., 6 = Saturday
            $table->time('opening_time')->nullable(); // Opening time for this day
            $table->time('closing_time')->nullable(); // Closing time for this day
            $table->boolean('is_closed')->default(false); // If true, branch is closed this day
            $table->timestamps();

            $table->unique(['branch_id', 'day_of_week']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_schedules');
    }
};
