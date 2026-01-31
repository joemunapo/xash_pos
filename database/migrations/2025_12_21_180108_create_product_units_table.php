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
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name', 50); // e.g., "Pack", "Carton", "Case"
            $table->string('abbreviation', 10); // e.g., "pack", "ctn"
            $table->integer('quantity'); // how many base units in this packaging
            $table->decimal('selling_price', 12, 2)->nullable(); // optional custom price
            $table->decimal('cost_price', 12, 2)->nullable(); // optional custom cost
            $table->string('barcode', 50)->nullable(); // packaging-specific barcode
            $table->boolean('is_default')->default(false); // default selling unit
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'abbreviation']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_units');
    }
};
