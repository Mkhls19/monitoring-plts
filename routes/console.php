<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:rekap-energi-harian')->dailyAt('23:55');

Artisan::command('plts:inject-today', function () {
    $this->info("Menghasilkan data Inverter untuk 24 jam terakhir...");

    $endDate = \Carbon\Carbon::now();
    $startDate = $endDate->copy()->subHours(24);

    $batteryCap = 50.0;
    $realtimeInserts = [];
    $pMax = 4000;

    for ($date = $startDate->copy(); $date->lte($endDate); $date->addMinutes(5)) {
        $timestamp = $date->copy();
        $hour = $timestamp->hour;
        $min = $timestamp->minute;
        $t = $hour + ($min / 60);

        $pv_power = 0;
        $pv_voltage = rand(10, 15);
        $pv_charge_current = 0;
        $ac_load = rand(50, 150);

        if ($t >= 6 && $t <= 17) {
            $x = ($t - 6) / 11 * M_PI; 
            $idealPower = $pMax * sin($x);
            
            if (rand(1, 100) > 85) {
                $idealPower *= rand(40, 70) / 100;
            }

            $pv_power = round($idealPower, 2);
            $pv_voltage = round(rand(320, 380) / 10, 2);
            $pv_charge_current = round($pv_power / $pv_voltage, 2);

            $batteryCap += ($pv_power * (5/60) / 2400) * 100;
            $ac_load = rand(150, 400);
        } else {
            $batteryCap -= ($ac_load * (5/60) / 2400) * 100;
        }

        if ($batteryCap > 100) $batteryCap = 100;
        if ($batteryCap < 15) $batteryCap = 15;

        $battery_voltage = 22 + ($batteryCap / 100 * 5);

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

        if (count($realtimeInserts) >= 500) {
            \App\Models\MonitoringRealtime::insert($realtimeInserts);
            $realtimeInserts = [];
        }
    }

    if (count($realtimeInserts) > 0) {
        \App\Models\MonitoringRealtime::insert($realtimeInserts);
    }

    $this->info("Berhasil! Silakan cek halaman Monitoring, grafik sudah kembali penuh.");
});
