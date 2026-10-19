<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EnergiHarian;

class GenerateLongTermForecast extends Command
{
    protected $signature = 'plts:forecast-long';
    protected $description = 'Calculate Out-of-Sample MAPE for 7 days and 30 days.';

    public function handle()
    {
        $this->info("Memulai Pengujian Out-of-Sample (Backtesting) tanpa menyentuh database...");

        $dailyData = EnergiHarian::orderBy('tanggal', 'asc')->get();
        $totalCount = $dailyData->count();

        if ($totalCount < 35) {
            $this->error("Data harian kurang dari 35 hari. Tidak bisa melakukan backtest 30 hari.");
            return;
        }

        // Parameter terbaik dari Laporan
        $alpha = 0.4; 
        $beta = 0.1;  

        // --- PENGUJIAN 1 MINGGU (7 HARI) ---
        $cutoff7 = $totalCount - 7 - 1;
        list($L7, $T7) = $this->calculateLandT($dailyData, $cutoff7, $alpha, $beta);
        
        $sumApe7 = 0;
        $this->info("\n=== HASIL BACKTEST 7 HARI ===");
        $this->table(['Hari Ke', 'Tanggal', 'Aktual (kWh)', 'Prediksi (kWh)', 'APE (%)'], $this->generateBacktestData($dailyData, $cutoff7, 7, $L7, $T7, $sumApe7));
        $mape7 = $sumApe7 / 7;
        $this->info(">> MAPE Prediksi 7 Hari: " . round($mape7, 2) . "%");

        // --- PENGUJIAN 1 BULAN (30 HARI) ---
        $cutoff30 = $totalCount - 30 - 1;
        list($L30, $T30) = $this->calculateLandT($dailyData, $cutoff30, $alpha, $beta);
        
        $sumApe30 = 0;
        $this->info("\n=== HASIL BACKTEST 30 HARI ===");
        $this->table(['Hari Ke', 'Tanggal', 'Aktual (kWh)', 'Prediksi (kWh)', 'APE (%)'], $this->generateBacktestData($dailyData, $cutoff30, 30, $L30, $T30, $sumApe30));
        $mape30 = $sumApe30 / 30;
        $this->info(">> MAPE Prediksi 30 Hari: " . round($mape30, 2) . "%");

        $this->info("\nSelesai! Sistem web dan database tetap 100% aman.");
    }

    private function calculateLandT($dailyData, $cutoffIndex, $alpha, $beta) {
        $L = $dailyData[0]->energi_pv_harian;
        $T = $dailyData[1]->energi_pv_harian - $dailyData[0]->energi_pv_harian;
        
        for ($i = 1; $i <= $cutoffIndex; $i++) {
            $actual = $dailyData[$i]->energi_pv_harian;
            $last_L = $L;
            $L = $alpha * $actual + (1 - $alpha) * ($last_L + $T);
            $T = $beta * ($L - $last_L) + (1 - $beta) * $T;
        }
        return [$L, $T];
    }

    private function generateBacktestData($dailyData, $cutoffIndex, $days, $L, $T, &$sumApe) {
        $rows = [];
        for ($m = 1; $m <= $days; $m++) {
            $forecast = max(0, $L + ($m * $T));
            $targetData = $dailyData[$cutoffIndex + $m];
            $actual = $targetData->energi_pv_harian;
            $tanggal = $targetData->tanggal->format('Y-m-d');
            
            $ape = 0;
            if ($actual > 0) {
                $ape = abs(($actual - $forecast) / $actual) * 100;
            }
            $sumApe += $ape;

            $rows[] = [
                $m, 
                $tanggal, 
                round($actual, 2), 
                round($forecast, 2), 
                round($ape, 2) . "%"
            ];
        }
        return $rows;
    }
}
