<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Charts\TopMenuChart;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TopMenuReportController extends Controller
{
    public function exportCsv(): StreamedResponse
    {
        $filter = request()->query('filter', '7d');
        $days = match ($filter) {
            '1d' => 1,
            '7d' => 7,
            '30d' => 30,
            default => 7,
        };
        $chart = app(TopMenuChart::class);
        $rows = $chart->getData($days)->map(fn ($item) => [$chart->getLabel() => $item->label, 'Total Porsi' => $item->total])->toArray();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="top_menu_terlaris_'.now()->format('YmdHis').'.csv"',
        ];

        $callback = function () use ($rows, $chart) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [$chart->getTitle()]);
            fputcsv($handle, [$chart->getLabel(), 'Total Porsi']);
            foreach ($rows as $row) {
                fputcsv($handle, [$row[$chart->getLabel()], $row['Total Porsi']]);
            }
            fclose($handle);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
