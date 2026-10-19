<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SensorData;
use Carbon\Carbon;

class DummySensorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Bersihkan data lama
        SensorData::truncate();

        $days = 7;
        $now = Carbon::now();
        $records = [];

        for ($i = $days; $i >= 0; $i--) {
            // Loop setiap 10 menit (144 data per hari)
            for ($minute = 0; $minute < 1440; $minute += 10) {
                $time = clone $now;
                $time->subDays($i)->startOfDay()->addMinutes($minute);
                
                // Jangan buat data ke depan dari waktu sekarang
                if ($time->isAfter($now)) {
                    continue;
                }

                $hour = $time->hour + $time->minute / 60.0;
                
                // Simulasi profil matahari (jam 6 pagi sampai 18 sore)
                if ($hour >= 6 && $hour <= 18) {
                    $sun_intensity = sin(pi() * ($hour - 6) / 12);
                    $v_in = 13.0 + (rand(-50, 80) / 100);
                    $i_in = (3.0 * $sun_intensity) + (rand(-20, 20) / 100);
                    if ($i_in < 0) $i_in = 0;
                    
                    $v_out = $v_in - (rand(10, 50) / 100);
                    $i_out = (rand(40, 120) / 100);
                } else {
                    $v_in = rand(0, 45) / 10;
                    $i_in = rand(-60, 0) / 100;
                    
                    $v_out = 12.5 + (rand(-20, 20) / 100);
                    $i_out = (rand(40, 80) / 100);
                }
                
                $p_in = $v_in * $i_in;
                $p_out = $v_out * $i_out;
                
                $eff = 0;
                if ($p_in > 0.1) {
                    $eff = ($p_out / $p_in) * 100;
                    if ($eff > 100) $eff = 100;
                }
                
                if (rand(1, 100) <= 5) { // 5% anomali
                    $i_in = rand(-60, -40) / 100;
                    $p_in = $v_in * $i_in;
                    $eff = 0;
                }

                $records[] = [
                    'voltage_in' => round($v_in, 2),
                    'current_in' => round($i_in, 2),
                    'power_in' => round($p_in, 2),
                    'voltage_out' => round($v_out, 2),
                    'current_out' => round($i_out, 2),
                    'power_out' => round($p_out, 2),
                    'efficiency' => round($eff, 2),
                    'timestamp' => $time->format('Y-m-d H:i:s'),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Insert dalam batch agar lebih cepat
        $chunks = array_chunk($records, 500);
        foreach ($chunks as $chunk) {
            SensorData::insert($chunk);
        }
    }
}
