<?php

namespace App\Http\Controllers\Admin;

use App\Models\Recap;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class RecapExportController
{
    public function exportPdf(): Response
    {
        $recaps = Recap::where('type', 'daily')
            ->orderByDesc('period_start')
            ->get();

        $totals = Recap::where('type', 'daily')
            ->selectRaw('COALESCE(SUM(total_revenue), 0) as total_revenue, COALESCE(SUM(total_orders), 0) as total_orders, COALESCE(SUM(total_items_sold), 0) as total_items_sold')
            ->first();

        $pdf = Pdf::loadView('admin.recap-pdf', [
            'recaps' => $recaps,
            'totals' => $totals,
        ]);

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('recap-harian.pdf');
    }
}
