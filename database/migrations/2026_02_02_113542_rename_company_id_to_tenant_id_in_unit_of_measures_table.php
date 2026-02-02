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
        Schema::table('unit_of_measures', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->renameColumn('company_id', 'tenant_id');
        });

        Schema::table('unit_of_measures', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unit_of_measures', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->renameColumn('tenant_id', 'company_id');
        });

        Schema::table('unit_of_measures', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });
    }
};
