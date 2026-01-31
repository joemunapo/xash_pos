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
        // Stock levels per branch
        Schema::create('stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->decimal('quantity', 12, 3)->default(0);
            $table->decimal('reserved_quantity', 12, 3)->default(0);
            $table->timestamps();

            $table->unique(['branch_id', 'product_id', 'variant_id']);
        });

        // Batches for expiry and lot tracking
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('batch_number');
            $table->date('expiry_date')->nullable();
            $table->date('manufacturing_date')->nullable();
            $table->decimal('cost_price', 12, 2)->nullable();
            $table->decimal('quantity_received', 12, 3)->default(0);
            $table->decimal('quantity_remaining', 12, 3)->default(0);
            $table->timestamps();

            $table->index(['branch_id', 'product_id', 'expiry_date']);
        });

        // Stock movements for audit trail
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // purchase, sale, adjustment, transfer_in, transfer_out, damage, return
            $table->decimal('quantity', 12, 3);
            $table->decimal('balance_after', 12, 3);
            $table->string('reference')->nullable(); // sale_id, grn_id, adjustment_id, etc.
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'product_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('batches');
        Schema::dropIfExists('stock');
    }
};
