<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class ReferenceNumber
{
    public static function generateJobReference(): string
    {
        return self::generate('file_requests', 'reference_number', 'STS');
    }

    public static function generateInvoiceReference(): string
    {
        return self::generate('invoices', 'reference_number', 'INV');
    }

    protected static function generate(string $table, string $column, string $prefix): string
    {
        $year = now()->year;

        $lastReference = DB::table($table)
            ->where($column, 'like', "{$prefix}-{$year}-%")
            ->orderByDesc($column)
            ->value($column);

        $sequence = $lastReference
            ? ((int) substr($lastReference, -5)) + 1
            : 1;

        return sprintf('%s-%d-%05d', $prefix, $year, $sequence);
    }
}
