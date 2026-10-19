<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MonitoringRealtime;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Cache;

class MonitoringController extends Controller
{
    public function receiveData(Request $request)
    {
        $data = $request->json()->all();

        if (empty($data)) {
            return response()->json(["error" => "No JSON payload provided"], 400);
        }

        try {
            $battery_voltage = floatval($data['battery_voltage'] ?? 0);
            $pv_voltage = floatval($data['pv_voltage'] ?? 0);
            $pv_charge_current = floatval($data['pv_charge_current'] ?? 0);
            $pv_power = floatval($data['pv_power'] ?? 0);
            $battery_capacity_percent = floatval($data['battery_capacity_percent'] ?? 0);
            
            $ac_input_voltage = floatval($data['ac_input_voltage'] ?? 0);
            $ac_output_voltage = floatval($data['ac_output_voltage'] ?? 0);
            $ac_charge_current = floatval($data['ac_charge_current'] ?? 0);
            $ac_charge_power = floatval($data['ac_charge_power'] ?? 0);
            $ac_input_frequency = floatval($data['ac_input_frequency'] ?? 0);
            $ac_output_frequency = floatval($data['ac_output_frequency'] ?? 0);
            $inverter_load_percent = floatval($data['inverter_load_percent'] ?? 0);

            $cacheData = [
                'battery_voltage' => $battery_voltage,
                'pv_voltage' => $pv_voltage,
                'pv_charge_current' => $pv_charge_current,
                'pv_power' => $pv_power,
                'battery_capacity_percent' => $battery_capacity_percent,
                'ac_input_voltage' => $ac_input_voltage,
                'ac_output_voltage' => $ac_output_voltage,
                'ac_charge_current' => $ac_charge_current,
                'ac_charge_power' => $ac_charge_power,
                'ac_input_frequency' => $ac_input_frequency,
                'ac_output_frequency' => $ac_output_frequency,
                'inverter_load_percent' => $inverter_load_percent,
                'timestamp' => now()
            ];

            // Cache for realtime Dashboard
            Cache::put('latest_sensor_data', $cacheData, now()->addMinutes(5));

            $lastDbSaveTime = Cache::get('last_db_save_time');
            $savedToDb = false;
            $newRecord = null;

            // Save to DB every 60 seconds
            if (!$lastDbSaveTime || abs(now()->diffInSeconds($lastDbSaveTime)) >= 60) {
                $newRecord = MonitoringRealtime::create($cacheData);
                Cache::put('last_db_save_time', now(), now()->addHours(1));
                $savedToDb = true;
            }

            return response()->json([
                "message" => "Data received", 
                "saved_to_db" => $savedToDb,
                "id" => $newRecord ? $newRecord->id : null
            ], 201);
        } catch (\Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    public function getLatestData()
    {
        $cachedRecord = Cache::get('latest_sensor_data');
        if ($cachedRecord) {
            return response()->json($cachedRecord);
        }

        $record = MonitoringRealtime::orderBy('created_at', 'desc')->first();
        if ($record) {
            return response()->json($record);
        }
        return response()->json(["message" => "No data available"], 404);
    }
}
