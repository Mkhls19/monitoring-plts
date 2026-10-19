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
        Schema::table('monitoring_realtimes', function (Blueprint $table) {
            if (Schema::hasColumn('monitoring_realtimes', 'timestamp')) {
                $table->dropColumn('timestamp');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monitoring_realtimes', function (Blueprint $table) {
            $table->timestamp('timestamp')->nullable();
        });
    }
};
