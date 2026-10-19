<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MonitoringRealtime;
use App\Models\SystemLog;

class HistoryController extends Controller
{
    public function getHistory(Request $request)
    {
        $limit = $request->query('limit', 1000);
        $range = $request->query('range', null); // 1h, 6h, 24h
        $startDate = $request->query('start_date', null);
        $endDate = $request->query('end_date', null);
        
        $query = MonitoringRealtime::query();
        
        // 1. Filter spesifik berdasarkan tanggal awal & akhir
        if ($startDate && $endDate) {
            $records = $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                             ->orderBy('created_at', 'desc')
                             ->get();
            return response()->json($records);
        }
        
        if ($range === '1h') {
            $query->where('created_at', '>=', now()->subHour());
        } elseif ($range === '6h') {
            $query->where('created_at', '>=', now()->subHours(6));
        } elseif ($range === '24h') {
            $query->where('created_at', '>=', now()->subHours(24));
        }

        // 2. Mode Default: Mengutamakan data khusus HARI INI
        $todayStart = now()->startOfDay();
        $todayRecords = MonitoringRealtime::where('created_at', '>=', $todayStart)
                                         ->orderBy('created_at', 'desc')
                                         ->get();

        if ($todayRecords->count() > 0) {
            $records = $todayRecords;
        } else {
            // Fallback jika belum ada data di hari ini, ambil 1000 data terbaru yang tersedia
            $safeLimit = min((int) $limit, 1000);
            $records = $query->orderBy('created_at', 'desc')->limit($safeLimit)->get();
        }

        $history = $records->values();

        // Tambahkan data Cache realtime agar update tiap detik tanpa menunggu 60 detik DB
        $latestCache = \Illuminate\Support\Facades\Cache::get('latest_sensor_data');
        if ($latestCache) {
            $lastDbRecord = $history->first(); // data paling baru (desc)
            $cacheTime = \Carbon\Carbon::parse($latestCache['timestamp'] ?? now());
            
            if (!$lastDbRecord || $cacheTime->gt(\Carbon\Carbon::parse($lastDbRecord->created_at))) {
                $latestObj = (object) $latestCache;
                $latestObj->created_at = $cacheTime;
                $history->prepend($latestObj);
            }
        }

        return response()->json($history);
    }
}
