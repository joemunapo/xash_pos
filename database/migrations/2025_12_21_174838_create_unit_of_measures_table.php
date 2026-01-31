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
        Schema::create('unit_of_measures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name', 50);
            $table->string('abbreviation', 10);
            $table->string('category')->nullable(); // weight, volume, length, count, etc.
            $table->boolean('is_base_unit')->default(false); // base unit for conversions
            $table->boolean('allow_decimal')->default(true);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['company_id', 'abbreviation']);
        });

        Schema::create('unit_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_unit_id')->constrained('unit_of_measures')->cascadeOnDelete();
            $table->foreignId('to_unit_id')->constrained('unit_of_measures')->cascadeOnDelete();
            $table->decimal('conversion_factor', 15, 6); // multiply from_unit by this to get to_unit
            $table->timestamps();

            $table->unique(['company_id', 'from_unit_id', 'to_unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_conversions');
        Schema::dropIfExists('unit_of_measures');
    }
};
