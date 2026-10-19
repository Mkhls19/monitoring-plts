<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EnergiHarian;
use App\Models\PrediksiDes;

class ReportController extends Controller
{
    public function index()
    {
        return view('pages.reports');
    }

    public function getData(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        if (!$startDate || !$endDate) {
            // Default to last 30 days if no date provided
            $endDate = now()->format('Y-m-d');
            $startDate = now()->subDays(30)->format('Y-m-d');
        }

        $records = EnergiHarian::leftJoin('prediksi_des', 'energi_harians.tanggal', '=', 'prediksi_des.tanggal_prediksi')
            ->whereBetween('energi_harians.tanggal', [$startDate, $endDate])
            ->orderBy('energi_harians.tanggal', 'asc')
            ->get([
                'energi_harians.tanggal',
                'energi_harians.energi_pv_harian as aktual',
                'energi_harians.psh',
                'prediksi_des.hasil_prediksi as prediksi'
            ]);

        $totalEnergi = 0;
        $totalPsh = 0;
        $totalError = 0;
        $count = 0;
        $errorCount = 0;
        
        $tableData = [];
        
        foreach ($records as $r) {
            $aktual = (float) $r->aktual;
            $prediksi = (float) $r->prediksi;
            $psh = (float) $r->psh;

            $totalEnergi += $aktual;
            $totalPsh += $psh;
            $count++;

            $errorAbs = abs($aktual - $prediksi);
            $errorPct = 0;
            
            if ($aktual > 0) {
                $errorPct = ($errorAbs / $aktual) * 100;
                $totalError += $errorPct;
                $errorCount++;
            }

            $tableData[] = [
                'tanggal' => $r->tanggal->format('Y-m-d'),
                'aktual' => round($aktual, 2),
                'prediksi' => round($prediksi, 2),
                'psh' => round($psh, 2),
                'error_abs' => round($errorAbs, 2),
                'error_pct' => round($errorPct, 2)
            ];
        }

        $avgEnergi = $count > 0 ? $totalEnergi / $count : 0;
        $avgPsh = $count > 0 ? $totalPsh / $count : 0;
        $avgMape = $errorCount > 0 ? $totalError / $errorCount : 0;

        return response()->json([
            'summary' => [
                'total_energi' => round($totalEnergi, 2),
                'avg_energi' => round($avgEnergi, 2),
                'avg_psh' => round($avgPsh, 2),
                'avg_mape' => round($avgMape, 2),
                'days_count' => $count
            ],
            'data' => $tableData
        ]);
    }
}
