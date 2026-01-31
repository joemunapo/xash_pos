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
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('currency', 3)->default('USD');
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();
            $table->string('receipt_header')->nullable();
            $table->string('receipt_footer')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        // Pivot table for user-branch assignments
        Schema::create('branch_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('cashier'); // manager, cashier, stockist
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['branch_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_user');
        Schema::dropIfExists('branches');
    }
};
