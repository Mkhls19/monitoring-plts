<?php

namespace App\Services;

use App\Models\EnergiHarian;
use App\Models\PrediksiDes;

class ForecastService
{
    /**
     * Menghitung peramalan energi harian menggunakan Double Exponential Smoothing (Holt's Method).
     *
     * @return array
     */
    public function calculateDailyForecast(): array
    {
        $dailyData = EnergiHarian::orderBy('tanggal', 'asc')->get();

        if ($dailyData->count() < 2) {
            return [
                "success" => false,
                "message" => "Not enough data for forecasting. Minimum 2 days required.",
                "data" => $dailyData
            ];
        }

        // Cari parameter Alpha dan Beta terbaik secara dinamis
        $optimalParams = $this->findOptimalParameters($dailyData);
        $alpha = $optimalParams['alpha'];
        $beta = $optimalParams['beta'];

        $historical = [];
        
        $L = $dailyData[0]->energi_pv_harian;
        $T = $dailyData[1]->energi_pv_harian - $dailyData[0]->energi_pv_harian;
        
        $sum_ape = 0;
        $count_ape = 0;

        foreach ($dailyData as $index => $day) {
            $actual = $day->energi_pv_harian;
            $forecast = 0;
            $ape = 0;

            if ($index > 0) {
                // The forecast for current day made from previous day's L and T
                $forecast = $L + $T;
                
                // Calculate MAPE for this point (if actual > 0 to avoid division by zero)
                if ($actual > 0) {
                    $ape = abs(($actual - $forecast) / $actual) * 100;
                    $sum_ape += $ape;
                    $count_ape++;
                }

                $last_L = $L;
                $L = $alpha * $actual + (1 - $alpha) * ($last_L + $T);
                $T = $beta * ($L - $last_L) + (1 - $beta) * $T;
            } else {
                $forecast = $actual; // Base case
            }

            $current_mape = $count_ape > 0 ? ($sum_ape / $count_ape) : 0;

            // Simpan atau update ke PrediksiDes
            PrediksiDes::updateOrCreate(
                ['tanggal_prediksi' => $day->tanggal],
                [
                    'data_aktual' => $actual,
                    'hasil_prediksi' => $forecast,
                    'alpha' => $alpha,
                    'beta' => $beta,
                    'level_lt' => $L,
                    'trend_tt' => $T,
                    'mape' => $current_mape
                ]
            );

            $historical[] = [
                'tanggal' => $day->tanggal->format('Y-m-d'),
                'aktual' => round($actual, 2),
                'prediksi' => round($forecast, 2),
            ];
        }

        // Forecast for the next day (t+1)
        $lastDate = end($historical)['tanggal'];
        $nextDate = date('Y-m-d', strtotime($lastDate . ' +1 day'));
        
        $forecast_next = $L + 1 * $T;
        $forecast_next = max(0, $forecast_next); // Ensure no negative energy
        $current_mape = $count_ape > 0 ? ($sum_ape / $count_ape) : 0;

        PrediksiDes::updateOrCreate(
            ['tanggal_prediksi' => $nextDate],
            [
                'data_aktual' => null, // Belum ada data aktual
                'hasil_prediksi' => $forecast_next,
                'alpha' => $alpha,
                'beta' => $beta,
                'level_lt' => $L,
                'trend_tt' => $T,
                'mape' => $current_mape
            ]
        );

        $forecast = [
            'tanggal' => $nextDate,
            'prediksi' => round($forecast_next, 2),
            'mape' => round($current_mape, 2)
        ];

        return [
            "success" => true,
            "historical" => $historical,
            "forecast" => $forecast
        ];
    }

    /**
     * Mencari parameter Alpha dan Beta terbaik secara dinamis
     * menggunakan algoritma Grid Search (81 Kombinasi)
     */
    private function findOptimalParameters($dailyData): array
    {
        $bestAlpha = 0.5;
        $bestBeta = 0.3;
        $lowestMape = PHP_FLOAT_MAX;

        for ($a = 1; $a <= 9; $a++) {
            $alpha = $a / 10;
            for ($b = 1; $b <= 9; $b++) {
                $beta = $b / 10;
                
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
                if ($mape < $lowestMape) {
                    $lowestMape = $mape;
                    $bestAlpha = $alpha;
                    $bestBeta = $beta;
                }
            }
        }

        return ['alpha' => $bestAlpha, 'beta' => $bestBeta];
    }
}
