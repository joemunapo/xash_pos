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
        // Products table indexes
        try {
            Schema::table('products', function (Blueprint $table) {
                $table->index(['company_id', 'is_active'], 'idx_products_company_active');
            });
        } catch (\Exception $e) {
            // Index already exists
        }

        try {
            Schema::table('products', function (Blueprint $table) {
                $table->index(['company_id', 'category_id', 'is_active'], 'idx_products_company_category_active');
            });
        } catch (\Exception $e) {
            // Index already exists
        }

        try {
            Schema::table('products', function (Blueprint $table) {
                $table->index('barcode', 'idx_products_barcode');
            });
        } catch (\Exception $e) {
            // Index already exists
        }

        try {
            Schema::table('products', function (Blueprint $table) {
                $table->index('sku', 'idx_products_sku');
            });
        } catch (\Exception $e) {
            // Index already exists
        }

        try {
            Schema::table('products', function (Blueprint $table) {
                $table->index('plu_code', 'idx_products_plu_code');
            });
        } catch (\Exception $e) {
            // Index already exists
        }

        // Categories table indexes
        try {
            Schema::table('categories', function (Blueprint $table) {
                $table->index(['company_id', 'is_active', 'parent_id'], 'idx_categories_company_active_parent');
            });
        } catch (\Exception $e) {
            // Index already exists
        }

        // Stock table indexes
        try {
            Schema::table('stock', function (Blueprint $table) {
                $table->index(['product_id', 'branch_id'], 'idx_stock_product_branch');
            });
        } catch (\Exception $e) {
            // Index already exists
        }

        try {
            Schema::table('stock', function (Blueprint $table) {
                $table->index('branch_id', 'idx_stock_branch');
            });
        } catch (\Exception $e) {
            // Index already exists
        }

        // Branch product prices table indexes
        try {
            Schema::table('branch_product_prices', function (Blueprint $table) {
                $table->index(['product_id', 'branch_id'], 'idx_branch_prices_product_branch');
            });
        } catch (\Exception $e) {
            // Index already exists
        }

        // Product units table indexes
        try {
            Schema::table('product_units', function (Blueprint $table) {
                $table->index('product_id', 'idx_product_units_product');
            });
        } catch (\Exception $e) {
            // Index already exists
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_company_active');
            $table->dropIndex('idx_products_company_category_active');
            $table->dropIndex('idx_products_barcode');
            $table->dropIndex('idx_products_sku');
            $table->dropIndex('idx_products_plu_code');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('idx_categories_company_active_parent');
        });

        Schema::table('stock', function (Blueprint $table) {
            $table->dropIndex('idx_stock_product_branch');
            $table->dropIndex('idx_stock_branch');
        });

        Schema::table('branch_product_prices', function (Blueprint $table) {
            $table->dropIndex('idx_branch_prices_product_branch');
        });

        Schema::table('product_units', function (Blueprint $table) {
            $table->dropIndex('idx_product_units_product');
        });
    }
};
