<?php

namespace App\Services\Reports;

use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExportService
{
    public function streamDownload(string $reportType, Collection $rows): StreamedResponse
    {
        $timestamp = now()->format('Ymd-Hi');
        $filename = "juntaos-{$reportType}-{$timestamp}.csv";

        return response()->streamDownload(function () use ($rows) {
            $output = fopen('php://output', 'w');

            if (! $output) {
                return;
            }

            // UTF-8 BOM for compatibility with spreadsheet software.
            fwrite($output, "\xEF\xBB\xBF");

            $firstRow = $rows->first();
            if (is_array($firstRow)) {
                fputcsv($output, array_keys($firstRow));
            }

            foreach ($rows as $row) {
                fputcsv($output, is_array($row) ? array_values($row) : (array) $row);
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
