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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('loyalty_tier')->default('bronze'); // bronze, silver, gold, platinum
            $table->decimal('loyalty_points', 12, 2)->default(0);
            $table->string('qr_code')->nullable()->unique();
            $table->json('metadata')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('member_since')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'phone']);
            $table->index(['company_id', 'email']);
        });

        // Loyalty transactions
        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // earn, redeem, adjust, expire
            $table->decimal('points', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->string('reference')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_transactions');
        Schema::dropIfExists('customers');
    }
};
