<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SystemSetting;
use App\Models\SystemLog;
use App\Models\EnergiHarian;
use App\Models\PrediksiDes;

class DashboardController extends Controller
{
    public function index()
    {
        $today = \Carbon\Carbon::today();
        
        // 1. Ambil data realtime hari ini
        $realtimeData = \App\Models\MonitoringRealtime::whereDate('created_at', $today)->get();
        
        // 2. Siapkan object kosong
        $todayEnergy = (object) [
            'energi_pv_harian' => 0,
            'energi_pv_bulanan' => 0,
            'psh' => 0,
            'tanggal' => $today
        ];

        // 3. Kalkulasi realtime (Rumus yang sama dengan rekap)
        if ($realtimeData->count() > 0) {
            $avg_pv_power = $realtimeData->avg('pv_power');
            $energi_pv_harian_realtime = ($avg_pv_power * 24) / 1000;
            $psh_realtime = ($energi_pv_harian_realtime * 1000) / 1000; // Asumsi 1000 Wp
            
            $todayEnergy->energi_pv_harian = round($energi_pv_harian_realtime, 2);
            $todayEnergy->psh = round($psh_realtime, 2);
        }

        // 4. Tambahkan dengan total bulanan dari hari kemarin
        $yesterdayRekap = EnergiHarian::orderBy('tanggal', 'desc')->first();
        if ($yesterdayRekap && \Carbon\Carbon::parse($yesterdayRekap->tanggal)->month == $today->month) {
            $todayEnergy->energi_pv_bulanan = round($yesterdayRekap->energi_pv_bulanan + $todayEnergy->energi_pv_harian, 2);
        } else {
            $todayEnergy->energi_pv_bulanan = $todayEnergy->energi_pv_harian;
        }

        $forecast = PrediksiDes::orderBy('tanggal_prediksi', 'desc')->first();
        
        return view('pages.dashboard', compact('todayEnergy', 'forecast'));
    }

    public function monitoring()
    {
        return view('pages.monitoring');
    }

    public function history()
    {
        return view('pages.history');
    }

    public function prediction()
    {
        $forecast = PrediksiDes::orderBy('tanggal_prediksi', 'desc')->first();
        
        // Parameter DES
        $latestParams = PrediksiDes::whereNotNull('data_aktual')->orderBy('tanggal_prediksi', 'desc')->first();
        $alpha = $latestParams ? $latestParams->alpha : 0.5;
        $beta = $latestParams ? $latestParams->beta : 0.3;
        $mape = $latestParams ? $latestParams->mape : 0;
        $trainingCount = PrediksiDes::whereNotNull('data_aktual')->count();
        
        $avgEnergy = EnergiHarian::avg('energi_pv_harian') ?? 0;
        return view('pages.prediction', compact('forecast', 'alpha', 'beta', 'mape', 'trainingCount', 'avgEnergy'));
    }

    public function reports()
    {
        return view('pages.reports');
    }

    public function logs()
    {
        $logs = SystemLog::orderBy('created_at', 'desc')->limit(100)->get();
        return view('pages.logs', compact('logs'));
    }
}
