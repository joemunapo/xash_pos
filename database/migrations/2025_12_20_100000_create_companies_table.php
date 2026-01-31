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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('trading_name')->nullable();
            $table->string('logo')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('vat_number')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('default_currency', 3)->default('USD');
            $table->tinyInteger('fiscal_year_start')->default(1); // Month 1-12
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add company_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('pin', 6)->nullable()->after('password');
            $table->string('avatar')->nullable()->after('pin');
            $table->boolean('is_active')->default(true)->after('avatar');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn(['company_id', 'pin', 'avatar', 'is_active', 'last_login_at']);
        });

        Schema::dropIfExists('companies');
    }
};
