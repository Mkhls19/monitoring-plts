<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MonitoringRealtime;
use App\Models\EnergiHarian;
use App\Models\PrediksiDes;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DummyPltsSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Mempersiapkan database... Menghapus data lama.');
        
        MonitoringRealtime::truncate();
        EnergiHarian::truncate();
        PrediksiDes::truncate();

        $historicalData = [
            '2026-02-23' => 2.38,
            '2026-02-24' => 3.21,
            '2026-02-25' => 3.50,
            '2026-02-27' => 1.63,
            '2026-03-01' => 2.02,
            '2026-03-02' => 2.37,
            '2026-03-03' => 1.28,
            '2026-03-04' => 2.23,
            '2026-03-05' => 2.13,
            '2026-03-08' => 1.25,
            '2026-03-15' => 1.75,
            '2026-03-20' => 0.66,
            '2026-03-25' => 2.64,
            '2026-03-27' => 1.34,
            '2026-03-29' => 0.77,
            '2026-04-05' => 2.86,
        ];

        $startDate = Carbon::create(2023, 8, 1);
        $endDate = Carbon::create(2026, 5, 16);

        $batteryCap = 50.0; // Start at 50%
        
        $energiBulanan = 0;
        $energiTotal = 0;
        $costBulanan = 0;
        $costTotal = 0;
        $tarif = 1500;
        $currentMonth = $startDate->month;

        $realtimeInserts = [];
        $weatherState = 0; // 0: Cerah, 1: Berawan, 2: Hujan

        $this->command->info("Memulai generate data dari {$startDate->format('Y-m-d')} hingga {$endDate->format('Y-m-d')}");

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateString = $date->format('Y-m-d');
            // Menentukan Musim (Seasonality)
            $isKemarau = ($date->month >= 5 && $date->month <= 9);

            // Markov Chain Transition
            $randWeather = rand(1, 100);
            if ($isKemarau) {
                // Musim Kemarau: Sangat jarang hujan, dominan cerah
                if ($weatherState === 0) { // Cerah
                    if ($randWeather <= 75) $weatherState = 0;
                    elseif ($randWeather <= 95) $weatherState = 1;
                    else $weatherState = 2;
                } elseif ($weatherState === 1) { // Berawan
                    if ($randWeather <= 60) $weatherState = 0;
                    elseif ($randWeather <= 90) $weatherState = 1;
                    else $weatherState = 2;
                } else { // Hujan
                    if ($randWeather <= 50) $weatherState = 0;
                    elseif ($randWeather <= 90) $weatherState = 1;
                    else $weatherState = 2;
                }
            } else {
                // Musim Hujan / Pancaroba: Peluang hujan dan berawan lebih besar
                if ($weatherState === 0) { // Cerah
                    if ($randWeather <= 40) $weatherState = 0;
                    elseif ($randWeather <= 80) $weatherState = 1;
                    else $weatherState = 2;
                } elseif ($weatherState === 1) { // Berawan
                    if ($randWeather <= 30) $weatherState = 0;
                    elseif ($randWeather <= 70) $weatherState = 1;
                    else $weatherState = 2;
                } else { // Hujan
                    if ($randWeather <= 20) $weatherState = 0;
                    elseif ($randWeather <= 60) $weatherState = 1;
                    else $weatherState = 2;
                }
            }

            // Tentukan target energi harian (kWh)
            if (isset($historicalData[$dateString])) {
                $targetEnergyKwh = $historicalData[$dateString];
            } else {
                if ($weatherState === 0) { // Cerah
                    $targetEnergyKwh = rand(200, 280) / 100;
                } elseif ($weatherState === 1) { // Berawan
                    $targetEnergyKwh = rand(100, 190) / 100;
                } else { // Hujan
                    $targetEnergyKwh = rand(20, 90) / 100;
                }
            }

            // Hitung akumulasi
            if ($date->month !== $currentMonth) {
                $energiBulanan = 0;
                $costBulanan = 0;
                $currentMonth = $date->month;
            }
            $energiBulanan += $targetEnergyKwh;
            $energiTotal += $targetEnergyKwh;
            $costBulanan += ($targetEnergyKwh * $tarif);
            $costTotal += ($targetEnergyKwh * $tarif);

            EnergiHarian::create([
                'tanggal' => $dateString,
                'energi_pv_harian' => $targetEnergyKwh,
                'energi_pv_bulanan' => $energiBulanan,
                'energi_pv_total' => $energiTotal,
                'month_cost_pv' => $costBulanan,
                'total_cost_pv' => $costTotal,
                'energi_charge_ac_harian' => $targetEnergyKwh * 0.8,
                'energi_charge_ac_bulanan' => $energiBulanan * 0.8,
                'energi_charge_ac_total' => $energiTotal * 0.8,
                'month_cost_charge_ac' => $costBulanan * 0.8,
                'total_cost_charge_ac' => $costTotal * 0.8,
                'psh' => $targetEnergyKwh,
            ]);

            // Bypass Realtime data generation untuk tanggal sebelum 30 hari terakhir
            if ($date->greaterThanOrEqualTo($endDate->copy()->subDays(30))) {
                // Pmax = Energy / 7.0 (integral factor of sine curve across 11 hours)
                $energyWh = $targetEnergyKwh * 1000;
                $pMax = $energyWh / 7.0;

            for ($hour = 0; $hour < 24; $hour++) {
                for ($min = 0; $min < 60; $min += 5) {
                    $timestamp = $date->copy()->setTime($hour, $min, 0);
                    $t = $hour + ($min / 60);

                    $pv_power = 0;
                    $pv_voltage = rand(10, 15);
                    $pv_charge_current = 0;
                    $ac_load = rand(50, 150); // Beban standar

                    if ($t >= 6 && $t <= 17) {
                        // Siang hari: Bell curve
                        $x = ($t - 6) / 11 * M_PI; 
                        $idealPower = $pMax * sin($x);
                        
                        // Efek cuaca mendung (drop acak)
                        if (rand(1, 100) > 85) {
                            $idealPower *= rand(40, 70) / 100;
                        }

                        $pv_power = round($idealPower, 2);
                        $pv_voltage = round(rand(320, 380) / 10, 2); // 32V - 38V
                        $pv_charge_current = round($pv_power / $pv_voltage, 2);

                        // Charge battery
                        $batteryCap += ($pv_power * (5/60) / 2400) * 100;
                        $ac_load = rand(150, 400); // Beban siang lebih besar
                    } else {
                        // Discharge battery
                        $batteryCap -= ($ac_load * (5/60) / 2400) * 100;
                    }

                    if ($batteryCap > 100) $batteryCap = 100;
                    if ($batteryCap < 15) $batteryCap = 15;

                    $battery_voltage = 22 + ($batteryCap / 100 * 5); // 22V to 27V

                    $realtimeInserts[] = [
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                        'battery_voltage' => round($battery_voltage, 2),
                        'pv_voltage' => $pv_voltage,
                        'pv_charge_current' => $pv_charge_current,
                        'pv_power' => $pv_power,
                        'battery_capacity_percent' => round($batteryCap, 2),
                        'ac_input_voltage' => rand(210, 230),
                        'ac_output_voltage' => rand(218, 222),
                        'ac_charge_current' => round($ac_load / 220, 2),
                        'ac_charge_power' => $ac_load,
                        'ac_input_frequency' => 50,
                        'ac_output_frequency' => 50,
                        'inverter_load_percent' => round(($ac_load / 3000) * 100, 2),
                    ];

                    if (count($realtimeInserts) >= 1000) {
                        MonitoringRealtime::insert($realtimeInserts);
                        $realtimeInserts = [];
                    }
                    }
                }
            } // End of Realtime bypass if
        }

        if (count($realtimeInserts) > 0) {
            MonitoringRealtime::insert($realtimeInserts);
        }

        $this->command->info('Menjalankan ulang ForecastService untuk mengisi tabel prediksi_des...');
        $forecastService = new \App\Services\ForecastService();
        $forecastService->calculateDailyForecast();

        $this->command->info('Seeding berhasil diselesaikan!');
    }
}
