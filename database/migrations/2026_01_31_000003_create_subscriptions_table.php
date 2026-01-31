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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('subscription_plan_id')->nullable()->constrained('subscription_plans')->nullOnDelete();
            $table->string('plan_name');
            $table->string('plan_slug');
            $table->decimal('price', 10, 2)->default(0);
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->integer('max_users')->default(5);
            $table->integer('max_branches')->default(1);
            $table->integer('max_products')->default(100);
            $table->json('features')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('renews_at')->nullable();
            $table->enum('status', ['active', 'cancelled', 'expired'])->default('active');
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('status');
            $table->index('ends_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
