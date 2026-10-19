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
        Schema::create('prediksi_des', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_prediksi')->nullable();
            $table->float('data_aktual')->nullable();
            $table->float('hasil_prediksi')->nullable();
            $table->float('alpha')->nullable();
            $table->float('beta')->nullable();
            $table->float('level_lt')->nullable();
            $table->float('trend_tt')->nullable();
            $table->float('mape')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prediksi_des');
    }
};
