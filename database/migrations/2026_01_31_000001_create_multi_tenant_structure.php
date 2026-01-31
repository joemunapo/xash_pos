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
        // Rename companies table to tenants
        Schema::rename('companies', 'tenants');

        // Add new columns to tenants table
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('slug')->unique()->after('name');
            $table->string('domain')->unique()->nullable()->after('slug');
            $table->enum('subscription_status', ['trial', 'active', 'suspended', 'cancelled'])
                ->default('trial')
                ->after('is_active');
            $table->timestamp('trial_ends_at')->nullable()->after('subscription_status');
        });

        // Update users table - rename company_id to tenant_id and add super admin flag
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('company_id', 'tenant_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_super_admin')->default(false)->after('role');
        });

        // Add tenant_id to branches table
        Schema::table('branches', function (Blueprint $table) {
            $table->renameColumn('company_id', 'tenant_id');
        });

        // Add tenant_id to categories table
        Schema::table('categories', function (Blueprint $table) {
            $table->renameColumn('company_id', 'tenant_id');
        });

        // Add tenant_id to products table
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('company_id', 'tenant_id');
        });

        // Add tenant_id to suppliers table
        Schema::table('suppliers', function (Blueprint $table) {
            $table->renameColumn('company_id', 'tenant_id');
        });

        // Add tenant_id to customers table
        Schema::table('customers', function (Blueprint $table) {
            $table->renameColumn('company_id', 'tenant_id');
        });

        // Add tenant_id to activity_logs table
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->renameColumn('company_id', 'tenant_id');
        });

        // Add indexes for performance
        Schema::table('users', function (Blueprint $table) {
            $table->index('tenant_id');
            $table->index('is_super_admin');
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->index('tenant_id');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->index('tenant_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index('tenant_id');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->index('tenant_id');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->index('tenant_id');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['tenant_id']);
            $table->dropIndex(['is_super_admin']);
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->dropIndex(['tenant_id']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['tenant_id']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['tenant_id']);
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropIndex(['tenant_id']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['tenant_id']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['tenant_id']);
        });

        // Rename back to company_id
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_super_admin');
            $table->renameColumn('tenant_id', 'company_id');
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->renameColumn('tenant_id', 'company_id');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->renameColumn('tenant_id', 'company_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('tenant_id', 'company_id');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->renameColumn('tenant_id', 'company_id');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->renameColumn('tenant_id', 'company_id');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->renameColumn('tenant_id', 'company_id');
        });

        // Remove new columns from tenants
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['slug', 'domain', 'subscription_status', 'trial_ends_at']);
        });

        // Rename tenants back to companies
        Schema::rename('tenants', 'companies');
    }
};
