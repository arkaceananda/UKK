<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Charts\ChartService;
use App\Services\Charts\SalesChart;
use App\Services\Charts\TopMenuChart;
use Illuminate\Http\JsonResponse;

class ChartDataController extends Controller
{
    public function sales(): JsonResponse
    {
        $filter = request()->query('filter', '7d');
        $days = match ($filter) {
            '1d' => 1,
            '7d' => 7,
            '30d' => 30,
            default => 7,
        };

        $service = new ChartService(new SalesChart);

        return response()->json([
            'totals' => $service->getTotals($days),
            'labels' => $service->getLabels($days),
        ]);
    }

    public function topMenu(): JsonResponse
    {
        $filter = request()->query('filter', '7d');
        $days = match ($filter) {
            '1d' => 1,
            '7d' => 7,
            '30d' => 30,
            default => 7,
        };

        $service = new ChartService(new TopMenuChart);

        return response()->json([
            'totals' => $service->getTotals($days),
            'labels' => $service->getLabels($days),
        ]);
    }
}
