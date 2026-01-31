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
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('from_currency', 3); // e.g., USD
            $table->string('to_currency', 3);   // e.g., ZIG, ZAR
            $table->decimal('rate', 12, 6);     // Exchange rate
            $table->date('effective_date');     // When this rate becomes effective
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Index for faster lookups (shortened name)
            $table->index(['company_id', 'from_currency', 'to_currency', 'effective_date'], 'exch_rate_lookup_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
