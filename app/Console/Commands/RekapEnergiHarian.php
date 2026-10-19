<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MonitoringRealtime;
use App\Models\EnergiHarian;
use Carbon\Carbon;

class RekapEnergiHarian extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:rekap-energi-harian {--date= : Tanggal spesifik (Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merekap data realtime harian menjadi data energi harian';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dateStr = $this->option('date') ?: Carbon::yesterday()->format('Y-m-d');
        $date = Carbon::parse($dateStr);
        
        $this->info("Merekap data energi untuk tanggal: {$date->format('Y-m-d')}");

        $data = MonitoringRealtime::whereDate('created_at', $date->format('Y-m-d'))->get();
        
        if ($data->isEmpty()) {
            $this->warn("Tidak ada data realtime pada tanggal tersebut.");
            return;
        }

        // Hitung rata-rata daya harian
        $avg_pv_power = $data->avg('pv_power'); // Watt
        $avg_ac_charge_power = $data->avg('ac_charge_power'); // Watt

        // Konversi ke Energi harian (kWh) asumsi 24 jam operasional
        $energi_pv_harian = ($avg_pv_power * 24) / 1000; 
        $energi_charge_ac_harian = ($avg_ac_charge_power * 24) / 1000;
        
        // PSH (Peak Sun Hours)
        $kapasitas_wp = 1000; // Default asumsi 1000 Wp
        $psh = ($energi_pv_harian * 1000) / $kapasitas_wp;

        $yesterday = $date->copy()->subDay();
        $recordKemarin = EnergiHarian::whereDate('tanggal', $yesterday->format('Y-m-d'))->first();
        
        $energi_pv_bulanan = $energi_pv_harian;
        $energi_pv_total = $energi_pv_harian;
        $energi_charge_ac_bulanan = $energi_charge_ac_harian;
        $energi_charge_ac_total = $energi_charge_ac_harian;

        if ($recordKemarin) {
            $energi_pv_total += $recordKemarin->energi_pv_total;
            $energi_charge_ac_total += $recordKemarin->energi_charge_ac_total;
            
            if ($date->month == $yesterday->month) {
                $energi_pv_bulanan += $recordKemarin->energi_pv_bulanan;
                $energi_charge_ac_bulanan += $recordKemarin->energi_charge_ac_bulanan;
            }
        }

        // Cost calculation (Tarif 1500/kWh)
        $tarif = 1500;
        $month_cost_pv = $energi_pv_bulanan * $tarif;
        $total_cost_pv = $energi_pv_total * $tarif;
        
        $month_cost_charge_ac = $energi_charge_ac_bulanan * $tarif;
        $total_cost_charge_ac = $energi_charge_ac_total * $tarif;

        EnergiHarian::updateOrCreate(
            ['tanggal' => $date->format('Y-m-d')],
            [
                'energi_pv_harian' => round($energi_pv_harian, 2),
                'energi_pv_bulanan' => round($energi_pv_bulanan, 2),
                'energi_pv_total' => round($energi_pv_total, 2),
                'month_cost_pv' => round($month_cost_pv, 2),
                'total_cost_pv' => round($total_cost_pv, 2),
                'energi_charge_ac_harian' => round($energi_charge_ac_harian, 2),
                'energi_charge_ac_bulanan' => round($energi_charge_ac_bulanan, 2),
                'energi_charge_ac_total' => round($energi_charge_ac_total, 2),
                'month_cost_charge_ac' => round($month_cost_charge_ac, 2),
                'total_cost_charge_ac' => round($total_cost_charge_ac, 2),
                'psh' => round($psh, 2)
            ]
        );

        $this->info("Rekap data energi berhasil disimpan.");
    }
}
