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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();
            $table->string('plu_code', 10)->nullable();
            $table->text('description')->nullable();
            $table->string('unit')->default('pcs'); // pcs, kg, g, l, ml
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->decimal('wholesale_price', 12, 2)->nullable();
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->boolean('is_taxable')->default(true);
            $table->string('image')->nullable();
            $table->boolean('track_stock')->default(true);
            $table->boolean('track_expiry')->default(false);
            $table->boolean('track_batches')->default(false);
            $table->boolean('allow_decimal_qty')->default(false);
            $table->integer('reorder_level')->default(0);
            $table->integer('reorder_quantity')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'sku']);
            $table->index(['company_id', 'barcode']);
            $table->index(['company_id', 'plu_code']);
        });

        // Product variants (size, color, weight)
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g., "500ml", "Large", "Red"
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();
            $table->decimal('price_modifier', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Branch-specific product pricing
        Schema::create('branch_product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('selling_price', 12, 2);
            $table->decimal('wholesale_price', 12, 2)->nullable();
            $table->timestamp('effective_from')->nullable();
            $table->timestamps();

            $table->unique(['branch_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_product_prices');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
    }
};
