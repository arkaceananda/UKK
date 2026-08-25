<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Charts\SalesChart;
use App\Services\Charts\TopMenuChart;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesReportController extends Controller
{
    public function exportSales(string $filter = '7d')
    {
        $days = match ($filter) {
            '1d' => 1,
            '7d' => 7,
            '30d' => 30,
            default => 7,
        };

        $chart = new SalesChart;
        $rows = $chart->getExportRows();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sales_report_'.$filter.'_'.now()->format('YmdHis').'.csv"',
        ];

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Waktu', 'Total Pendapatan']);
            foreach ($rows as $row) {
                fputcsv($handle, [$row['Waktu'], $row['Total (Rp)']]);
            }
            fclose($handle);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    public function exportTopMenu(string $filter = '7d')
    {
        $days = match ($filter) {
            '1d' => 1,
            '7d' => 7,
            '30d' => 30,
            default => 7,
        };

        $chart = new TopMenuChart;
        $rows = $chart->getExportRows();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="top_menu_report_'.$filter.'_'.now()->format('YmdHis').'.csv"',
        ];

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Menu', 'Total Porsi Terjual']);
            foreach ($rows as $row) {
                fputcsv($handle, [$row['Menu'], $row['Total Porsi']]);
            }
            fclose($handle);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
