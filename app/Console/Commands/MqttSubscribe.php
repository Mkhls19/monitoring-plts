<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use App\Models\MonitoringRealtime;
use Illuminate\Support\Facades\Cache;

class MqttSubscribe extends Command
{
    protected $signature = 'mqtt:subscribe';
    protected $description = 'Subscribe to EMQX Broker to receive PLTS data';

    public function handle()
    {
        $server   = 'broker.emqx.io';
        $port     = 1883;
        $clientId = 'laravel_sub_' . uniqid();
        $username = null; // Gunakan null, jangan string kosong
        $password = null;

        $mqtt = new MqttClient($server, $port, $clientId);

        $settings = (new ConnectionSettings)
            ->setKeepAliveInterval(60);
            
        if ($username !== null) {
            $settings->setUsername($username)->setPassword($password);
        }

        while (true) {
            try {
                $this->info("Menghubungkan ke MQTT Broker ($server:$port) ...");
                $mqtt->connect($settings, true);
                
                $topic = 'mppt_techfine_samoto_amsan/hybrid.techfine.009/alldata';
                $this->info("Berhasil terhubung! Mendengarkan topik: [$topic]");
                $this->info("Menunggu aliran data dari Inverter (Modbus)... Tekan Ctrl+C untuk berhenti.");

                $mqtt->subscribe($topic, function ($topic, $message) {
                    $this->processMessage($message);
                }, 0);

                $mqtt->loop(true);
                
            } catch (\Throwable $e) {
                $this->error("MQTT Error: " . $e->getMessage());
                $this->info("Mencoba koneksi ulang dalam 5 detik...");
                sleep(5);
            }
        }
    }

    private function processMessage($jsonPayload)
    {
        // ----------------- LOGIKA OTOMATISASI REKAP -----------------
        $currentDate = date('Y-m-d');
        $lastRecapDate = Cache::get('last_recap_date', '2000-01-01');
        
        // Jika hari sudah berganti (melewati 00:00) dan belum direkap
        if ($lastRecapDate !== $currentDate && date('H:i') >= '00:01') {
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            $this->info("\n[SYSTEM] Pergantian hari terdeteksi! Memulai rekap otomatis untuk tanggal: $yesterday");
            
            try {
                \Illuminate\Support\Facades\Artisan::call('app:rekap-energi-harian', ['--date' => $yesterday]);
                $this->info(\Illuminate\Support\Facades\Artisan::output());
                Cache::put('last_recap_date', $currentDate);
            } catch (\Exception $e) {
                $this->error("Gagal melakukan auto-rekap: " . $e->getMessage());
            }
        }
        // ------------------------------------------------------------

        // Bersihkan payload dari karakter aneh seperti \r atau \n
        $cleanPayload = trim(preg_replace('/[\x00-\x1F\x7F]/', '', $jsonPayload));
        
        $data = json_decode($cleanPayload, true);
        if ($data === null || !is_array($data)) {
            $this->error("Payload JSON invalid!");
            return;
        }

        // VALIDASI: Tolak payload palsu/retained message yang tidak punya 'batvolt'
        if (!isset($data['batvolt'])) {
            $this->warn(" ⚠️ MENGABAIKAN PAYLOAD PALSU (Data Sampah dari Broker): " . $cleanPayload);
            return;
        }

        $this->info("\n[RAW PAYLOAD DARI GATEWAY]:\n" . $cleanPayload);

        try {
            // Gabungkan seluruh data asli MQTT dengan data mapping untuk DB
            $cacheData = array_merge($data, [
                'battery_voltage' => floatval($data['batvolt'] ?? 0),
                'pv_voltage' => floatval($data['pvvolt'] ?? 0),
                'pv_charge_current' => floatval($data['pvcurrent'] ?? 0),
                'pv_power' => floatval($data['pvpower'] ?? 0),
                'battery_capacity_percent' => floatval($data['batcap'] ?? 0),
                'ac_input_voltage' => floatval($data['gridacvolt'] ?? 0),
                'ac_output_voltage' => floatval($data['invertervolt'] ?? 0),
                'ac_charge_current' => floatval($data['chargecurrent'] ?? 0),
                'ac_charge_power' => floatval($data['gridacpower'] ?? 0),
                'ac_input_frequency' => floatval($data['gridacfreq'] ?? 0),
                'ac_output_frequency' => floatval($data['inverterfreq'] ?? 0),
                'inverter_load_percent' => floatval($data['loadpercen'] ?? 0),
                'timestamp' => now()
            ]);

            // Cache untuk grafik real-time di Dashboard/Monitoring
            Cache::put('latest_sensor_data', $cacheData, now()->addMinutes(5));

        $lastDbSaveTime = Cache::get('last_db_save_time');
        
        // Simpan ke DB setiap 60 detik agar tidak penuh
        if (!$lastDbSaveTime || abs(now()->diffInSeconds($lastDbSaveTime)) >= 60) {
            $dbData = [
                'battery_voltage' => $cacheData['battery_voltage'],
                'pv_voltage' => $cacheData['pv_voltage'],
                'pv_charge_current' => $cacheData['pv_charge_current'],
                'pv_power' => $cacheData['pv_power'],
                'battery_capacity_percent' => $cacheData['battery_capacity_percent'],
                'ac_input_voltage' => $cacheData['ac_input_voltage'],
                'ac_output_voltage' => $cacheData['ac_output_voltage'],
                'ac_charge_current' => $cacheData['ac_charge_current'],
                'ac_charge_power' => $cacheData['ac_charge_power'],
                'ac_input_frequency' => $cacheData['ac_input_frequency'],
                'ac_output_frequency' => $cacheData['ac_output_frequency'],
                'inverter_load_percent' => $cacheData['inverter_load_percent'],
            ];
            MonitoringRealtime::create($dbData);
            
            Cache::put('last_db_save_time', now(), now()->addHours(1));
            $this->info(" >> Data MQTT berhasil disimpan ke database MySQL.");
        }

        } catch (\Exception $e) {
            $this->error("Gagal memproses/menyimpan data: " . $e->getMessage());
        }
    }
}
