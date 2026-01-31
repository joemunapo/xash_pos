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
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['tax_rate', 'opening_time', 'closing_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->decimal('tax_rate', 5, 2)->default(0)->after('currency');
            $table->time('opening_time')->nullable()->after('receipt_footer');
            $table->time('closing_time')->nullable()->after('opening_time');
        });
    }
};
