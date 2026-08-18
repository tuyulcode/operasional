<?php

namespace App\Support;

class NumberFormatter
{
    public static function parseId(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $s = trim((string) $value);

        if ($s === '' || ! preg_match('/^-?\d[\d.,]*$/', $s)) {
            return null;
        }

        if (str_contains($s, ',')) {
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
        } elseif (preg_match('/^-?\d{1,3}(\.\d{3})+$/', $s)) {
            $s = str_replace('.', '', $s);
        }

        return is_numeric($s) ? (float) $s : null;
    }

    public static function formatId(mixed $value, int $decimals = 0, ?string $prefix = null): string
    {
        $formatted = number_format((float) $value, $decimals, ',', '.');

        return $prefix === null ? $formatted : $prefix.$formatted;
    }
}
