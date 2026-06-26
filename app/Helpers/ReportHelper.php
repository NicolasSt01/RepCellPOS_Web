<?php

namespace App\Helpers;

class ReportHelper
{
    public static function formatMoney($amount): string
    {
        return '$' . number_format((float) $amount, 2);
    }

    public static function formatDate($date, string $format = 'd/m/Y'): string
    {
        if (! $date) return '—';
        if (is_string($date)) $date = \Carbon\Carbon::parse($date);
        return $date->format($format);
    }

    public static function porcentaje(float $part, float $total, int $decimals = 1): string
    {
        if ($total == 0) return '0%';
        return number_format(($part / $total) * 100, $decimals) . '%';
    }

    public static function diffPercent(float $current, float $previous): array
    {
        if ($previous == 0) return ['pct' => 0, 'direction' => 'neutral'];
        $pct = (($current - $previous) / abs($previous)) * 100;
        return [
            'pct' => round($pct, 1),
            'direction' => $pct > 0 ? 'up' : ($pct < 0 ? 'down' : 'neutral'),
        ];
    }
}
