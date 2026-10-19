<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EnergiHarian;

class CalculateGridSearch extends Command
{
    protected $signature = 'plts:grid-search';
    protected $description = 'Melakukan perhitungan 81 Kombinasi Alpha dan Beta untuk mencari MAPE terendah.';

    public function handle()
    {
        $this->info("Memulai Perhitungan Trial and Error (Grid Search) 81 Kombinasi...");

        $dailyData = EnergiHarian::orderBy('tanggal', 'asc')->get();
        $totalCount = $dailyData->count();

        if ($totalCount < 2) {
            $this->error("Data harian kurang dari 2 hari.");
            return;
        }

        $results = [];

        // Looping Alpha (0.1 to 0.9)
        for ($a = 1; $a <= 9; $a++) {
            $alpha = $a / 10;
            // Looping Beta (0.1 to 0.9)
            for ($b = 1; $b <= 9; $b++) {
                $beta = $b / 10;

                // Inisialisasi awal
                $L = $dailyData[0]->energi_pv_harian;
                $T = $dailyData[1]->energi_pv_harian - $dailyData[0]->energi_pv_harian;
                
                $sum_ape = 0;
                $count_ape = 0;

                foreach ($dailyData as $index => $day) {
                    $actual = $day->energi_pv_harian;
                    
                    if ($index > 0) {
                        $forecast = $L + $T;
                        
                        if ($actual > 0) {
                            $ape = abs(($actual - $forecast) / $actual) * 100;
                            $sum_ape += $ape;
                            $count_ape++;
                        }

                        $last_L = $L;
                        $L = $alpha * $actual + (1 - $alpha) * ($last_L + $T);
                        $T = $beta * ($L - $last_L) + (1 - $beta) * $T;
                    }
                }

                $mape = $count_ape > 0 ? ($sum_ape / $count_ape) : 0;
                
                $results[] = [
                    'alpha' => $alpha,
                    'beta' => $beta,
                    'mape' => $mape
                ];
            }
        }

        // Sorting dari MAPE terkecil (Terbaik) ke Terbesar
        usort($results, function ($item1, $item2) {
            return $item1['mape'] <=> $item2['mape'];
        });

        // Tampilkan pemenang di terminal
        $best = $results[0];
        $this->info("\n=== HASIL TERBAIK ===");
        $this->info("Alpha Terbaik : " . $best['alpha']);
        $this->info("Beta Terbaik  : " . $best['beta']);
        $this->info("MAPE Terendah : " . round($best['mape'], 2) . "%");

        // Direktori output
        $outputDir = base_path('Laporan');
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        // --- EXPORT KE CSV ---
        $csvPath = $outputDir . '/trial_error_mape.csv';
        $file = fopen($csvPath, 'w');
        fputcsv($file, ['Alpha (α)', 'Beta (β)', 'MAPE (%)']);

        foreach ($results as $row) {
            fputcsv($file, [
                $row['alpha'], 
                $row['beta'], 
                round($row['mape'], 2) . '%'
            ]);
        }
        fclose($file);
        
        $this->info("\n✅ Berhasil membuat file: Laporan/trial_error_mape.csv");
    }
}
