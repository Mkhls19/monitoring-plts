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
        Schema::create('monitoring_realtimes', function (Blueprint $table) {
            $table->id();
            
            // PARAMETER DC
            $table->float('battery_voltage')->nullable();
            $table->float('pv_voltage')->nullable();
            $table->float('pv_charge_current')->nullable();
            $table->float('pv_power')->nullable();
            $table->float('battery_capacity_percent')->nullable();

            // PARAMETER AC
            $table->float('ac_input_voltage')->nullable();
            $table->float('ac_output_voltage')->nullable();
            $table->float('ac_charge_current')->nullable();
            $table->float('ac_charge_power')->nullable();
            $table->float('ac_input_frequency')->nullable();
            $table->float('ac_output_frequency')->nullable();
            $table->float('inverter_load_percent')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitoring_realtimes');
    }
};
