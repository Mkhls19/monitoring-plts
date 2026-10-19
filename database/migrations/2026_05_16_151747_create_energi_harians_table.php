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
        Schema::create('energi_harians', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->nullable();

            // ENERGI PV
            $table->float('energi_pv_harian')->nullable();
            $table->float('energi_pv_bulanan')->nullable();
            $table->float('energi_pv_total')->nullable();

            // COST PV
            $table->float('month_cost_pv')->nullable();
            $table->float('total_cost_pv')->nullable();

            // ENERGI CHARGE AC
            $table->float('energi_charge_ac_harian')->nullable();
            $table->float('energi_charge_ac_bulanan')->nullable();
            $table->float('energi_charge_ac_total')->nullable();

            // COST CHARGE AC
            $table->float('month_cost_charge_ac')->nullable();
            $table->float('total_cost_charge_ac')->nullable();

            $table->float('psh')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('energi_harians');
    }
};
