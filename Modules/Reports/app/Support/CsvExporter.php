<?php

namespace Modules\Reports\Support;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Small dependency-free CSV exporter used by every report's "export" endpoint.
 * Accepts a list of header columns and an array of associative rows.
 */
class CsvExporter
{
    public function fromRows(string $filename, array $columns, array $rows): StreamedResponse
    {
        return new StreamedResponse(function () use ($columns, $rows) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM so Excel opens the file with correct encoding.
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, array_values($columns));

            foreach ($rows as $row) {
                $line = [];
                foreach (array_keys($columns) as $key) {
                    $line[] = $row[$key] ?? '';
                }
                fputcsv($handle, $line);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-store',
        ]);
    }

    public function stream(StreamedResponse $response): Response
    {
        return $response;
    }
}