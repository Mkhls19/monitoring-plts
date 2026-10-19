<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ForecastService;

class ForecastController extends Controller
{
    public function getEnergyForecast(Request $request, ForecastService $forecastService)
    {
        $result = $forecastService->calculateDailyForecast();
        
        if (!$result['success']) {
            return response()->json([
                "message" => $result['message'],
                "data" => $result['data'] ?? []
            ], 200);
        }

        return response()->json([
            'historical' => $result['historical'],
            'forecast' => $result['forecast']
        ]);
    }
}
