<?php

use App\Support\CsvExport;

it('neutralises values that a spreadsheet would run as formulas', function (string $value) {
    expect(CsvExport::safeCell($value))->toBe("'".$value);
})->with(['=1+1', '+256772123456', '-5', '@SUM(A1)', "\tTAB"]);

it('leaves ordinary values alone', function (mixed $value) {
    expect(CsvExport::safeCell($value))->toBe($value);
})->with(['Grace Achieng', 'LKB-7K3QX9MD', '', 2_000_000, null]);
