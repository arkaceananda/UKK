<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait HasChart
{
    abstract public function chartTitle(): string;

    abstract public function chartLabel(): string;

    abstract public function chartData(int $days): Collection;

    public function getDaysFromFilter(): int
    {
        return match ($this->filter) {
            '1d' => 1,
            '7d' => 7,
            '30d' => 30,
            default => 7,
        };
    }

    public function getExportData(): array
    {
        $data = $this->chartData($this->getDaysFromFilter());

        return $data->map(fn ($item) => [
            $this->chartLabel() => $item->label,
            'Total' => $item->total,
        ])->toArray();
    }

    public function exportCsv(): StreamedResponse
    {
        $data = $this->getExportData();
        $title = $this->chartTitle();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.str_replace(' ', '_', $title).'_'.now()->format('YmdHis').'.csv"',
        ];

        $callback = function () use ($data, $title) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [$title]);
            fputcsv($handle, [$this->chartLabel(), 'Total']);
            foreach ($data as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
