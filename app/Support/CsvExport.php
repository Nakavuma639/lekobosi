<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExport
{
    /**
     * Stream rows as a CSV download that opens cleanly in Excel.
     *
     * @param  list<string>  $headings
     * @param  iterable<array<int, scalar|null>>  $rows
     */
    public static function download(string $filename, array $headings, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headings, $rows): void {
            $handle = fopen('php://output', 'w');

            // UTF-8 byte order mark so Excel shows names with accents correctly.
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headings, escape: '');

            foreach ($rows as $row) {
                fputcsv($handle, array_map(self::safeCell(...), $row), escape: '');
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Neutralise spreadsheet formulas. Customer-typed text such as
     * "=HYPERLINK(...)" would otherwise run when the owner opens the file.
     */
    public static function safeCell(mixed $value): mixed
    {
        if (is_string($value) && $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$value;
        }

        return $value;
    }
}
