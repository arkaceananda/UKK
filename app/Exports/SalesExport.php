<?php

namespace App\Exports;

use App\Models\Transaksi;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesExport implements FromCollection, WithHeadings, WithMapping
{
    protected string $filter;

    public function __construct(string $filter)
    {
        $this->filter = $filter;
    }

    public function collection()
    {
        $startDate = match ($this->filter) {
            '1d' => Carbon::now()->subDays(1),
            '7d' => Carbon::now()->subDays(7),
            '30d' => Carbon::now()->subDays(30),
            default => Carbon::now()->subDays(7), // Default 7 hari
        };

        return Transaksi::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, SUM(total) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Total Pendapatan',
        ];
    }

    public function map($row): array
    {
        return [
            Carbon::parse($row->date)->format('Y-m-d'),
            (float) $row->total, // Pastikan format numerik
        ];
    }
}
