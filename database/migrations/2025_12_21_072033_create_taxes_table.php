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
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g., "VAT", "Sales Tax", "GST"
            $table->decimal('rate', 5, 2); // Tax percentage (e.g., 15.00 for 15%)
            $table->date('start_date')->nullable(); // When tax becomes effective
            $table->date('end_date')->nullable(); // When tax expires (null = no end)
            $table->boolean('is_default')->default(false); // Default tax for new products
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxes');
    }
};
