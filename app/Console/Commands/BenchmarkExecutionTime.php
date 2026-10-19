<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MonitoringRealtime;
use App\Models\EnergiHarian;
use Illuminate\Support\Facades\DB;

class BenchmarkExecutionTime extends Command
{
    /**
     * Nama dan tanda tangan perintah console.
     *
     * @var string
     */
    protected $signature = 'benchmark:execution-time';

    /**
     * Deskripsi perintah console.
     *
     * @var string
     */
    protected $description = 'Mengukur execution time penyimpanan data (Insert) dan pemanggilan data (Query) untuk revisi Pak Faisol.';

    /**
     * Eksekusi perintah console.
     */
    public function handle()
    {
        $this->info('========================================================================');
        $this->info('  PENGUJIAN EXECUTION TIME CLOUD SERVER & DATABASE (REVISI PAK FAISOL)  ');
        $this->info('========================================================================');
        $this->line('Host Server : VPS IDCloudHost (Ubuntu 22.04 LTS, RAM 2.0 GB, 20 GB SSD)');
        $this->line('Waktu Uji   : ' . now()->format('Y-m-d H:i:s'));
        $this->line('');

        // ---------------------------------------------------------------------
        // SKENARIO A: PENGUJIAN PENYIMPANAN DATA (INSERT MQTT SENSOR PAYLOAD)
        // ---------------------------------------------------------------------
        $this->info('--- [SKENARIO A] Pengujian Waktu Eksekusi Penyimpanan Data (Insert) ---');
        $this->line('Menguji simpan 1 payload sensor MQTT dari ESP32 (10x pengulangan)...');

        $insertTimes = [];
        for ($i = 1; $i <= 10; $i++) {
            $start = microtime(true);
            
            DB::beginTransaction();
            MonitoringRealtime::create([
                'pv_voltage' => 54.20,
                'pv_charge_current' => 12.50,
                'pv_power' => 677.50,
                'battery_voltage' => 51.80,
                'battery_capacity_percent' => 88,
                'ac_input_voltage' => 220.50,
                'ac_output_voltage' => 219.80,
                'inverter_load_percent' => 35,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::rollBack();
            
            $end = microtime(true);
            $durationMs = ($end - $start) * 1000;
            $insertTimes[] = $durationMs;
            $this->line("  Percobaan {$i} : " . number_format($durationMs, 2) . " ms");
        }

        $avgInsert = array_sum($insertTimes) / count($insertTimes);
        $minInsert = min($insertTimes);
        $maxInsert = max($insertTimes);

        $this->info(">> HASIL SKENARIO A (INSERT):");
        $this->line("   - Rata-rata Waktu Simpan (Average) : " . number_format($avgInsert, 2) . " ms");
        $this->line("   - Waktu Tercepat (Minimum)         : " . number_format($minInsert, 2) . " ms");
        $this->line("   - Waktu Terlambat (Maximum)        : " . number_format($maxInsert, 2) . " ms");
        $this->line('');

        // ---------------------------------------------------------------------
        // SKENARIO B: PENGUJIAN PEMANGGILAN DATA (QUERY FILTERING)
        // ---------------------------------------------------------------------
        $this->info('--- [SKENARIO B] Pengujian Waktu Eksekusi Pemanggilan Data (Query) ---');

        $queries = [
            [
                'label' => 'Filter 1 Hari Terakhir',
                'callback' => function () {
                    return MonitoringRealtime::where('created_at', '>=', now()->subDay())->get();
                }
            ],
            [
                'label' => 'Filter 1 Minggu Terakhir',
                'callback' => function () {
                    return MonitoringRealtime::where('created_at', '>=', now()->subDays(7))->get();
                }
            ],
            [
                'label' => 'Filter 1 Bulan Terakhir',
                'callback' => function () {
                    return MonitoringRealtime::where('created_at', '>=', now()->subDays(30))->get();
                }
            ],
            [
                'label' => 'Total Data Energi Harian',
                'callback' => function () {
                    return EnergiHarian::orderBy('tanggal', 'desc')->get();
                }
            ]
        ];

        $queryResultsTable = [];

        foreach ($queries as $q) {
            $times = [];
            $rowCount = 0;
            $memBefore = memory_get_usage();

            for ($i = 0; $i < 10; $i++) {
                $start = microtime(true);
                $res = call_user_func($q['callback']);
                $end = microtime(true);
                $rowCount = count($res);
                $times[] = ($end - $start) * 1000;
            }

            $memAfter = memory_get_peak_usage();
            $memUsedMb = ($memAfter - $memBefore) / 1024 / 1024;
            $avgQuery = array_sum($times) / count($times);
            $minQuery = min($times);
            $maxQuery = max($times);

            $queryResultsTable[] = [
                'Skenario Query' => $q['label'],
                'Jumlah Data' => number_format($rowCount) . ' baris',
                'Rata-rata (ms)' => number_format($avgQuery, 2) . ' ms',
                'Min (ms)' => number_format($minQuery, 2) . ' ms',
                'Max (ms)' => number_format($maxQuery, 2) . ' ms',
                'Penggunaan RAM' => number_format($memUsedMb, 2) . ' MB',
            ];
        }

        $this->table(
            ['Skenario Query', 'Jumlah Data', 'Rata-rata (ms)', 'Min (ms)', 'Max (ms)', 'Penggunaan RAM'],
            $queryResultsTable
        );

        $this->info('========================================================================');
        $this->info('  PENGUJIAN BENCHMARK SELESAI DENGAN SUKSES & AMAN TANPA EFEK SAMPING  ');
        $this->info('========================================================================');

        return 0;
    }
}
