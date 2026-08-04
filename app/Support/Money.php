<?php

namespace App\Support;

/**
 * Integer money helpers — never float intermediate for persisted amounts.
 */
class Money
{
    public const FX_SCALE = 1_000_000;

    /**
     * PKR major units string → paisa.
     */
    public static function pkrToPaisa(string|float|int $major): int
    {
        $normalized = is_string($major)
            ? str_replace([',', ' '], '', trim($major))
            : (string) $major;

        if ($normalized === '' || ! is_numeric($normalized)) {
            return 0;
        }

        // Avoid float: split at decimal point
        if (! str_contains($normalized, '.')) {
            return (int) $normalized * 100;
        }

        [$whole, $frac] = explode('.', $normalized, 2);
        $frac = substr(str_pad(preg_replace('/\D/', '', $frac) ?? '', 2, '0'), 0, 2);
        $sign = str_starts_with($whole, '-') ? -1 : 1;
        $whole = ltrim($whole, '+-');

        return $sign * (((int) $whole * 100) + (int) $frac);
    }

    public static function paisaToMajor(int $paisa): string
    {
        $sign = $paisa < 0 ? '-' : '';
        $abs = abs($paisa);

        return $sign.number_format($abs / 100, 2, '.', '');
    }

    public static function paisaFormatted(int $paisa, string $currency = 'PKR'): string
    {
        $sign = $paisa < 0 ? '-' : '';
        $abs = abs($paisa);

        return $sign.number_format($abs / 100, 2).' '.$currency;
    }

    /**
     * USD major → cents.
     */
    public static function usdToCents(string|float|int $major): int
    {
        return self::pkrToPaisa($major); // same 2dp minor units
    }

    /**
     * Human FX rate "278.50" → e6 integer (278_500_000).
     */
    public static function fxRateToE6(string|float|int $rate): int
    {
        $normalized = is_string($rate)
            ? str_replace([',', ' '], '', trim($rate))
            : (string) $rate;

        if ($normalized === '' || ! is_numeric($normalized)) {
            return 0;
        }

        if (! str_contains($normalized, '.')) {
            return (int) $normalized * self::FX_SCALE;
        }

        [$whole, $frac] = explode('.', $normalized, 2);
        $frac = substr(str_pad(preg_replace('/\D/', '', $frac) ?? '', 6, '0'), 0, 6);
        $whole = ltrim($whole, '+-');

        return ((int) $whole * self::FX_SCALE) + (int) $frac;
    }

    public static function fxRateFromE6(int $e6): string
    {
        $whole = intdiv($e6, self::FX_SCALE);
        $frac = str_pad((string) ($e6 % self::FX_SCALE), 6, '0', STR_PAD_LEFT);
        $frac = rtrim($frac, '0');
        if ($frac === '') {
            return (string) $whole;
        }

        return $whole.'.'.$frac;
    }

    /**
     * Convert USD cents × frozen FX rate → PKR paisa.
     * 100 cents (= $1) × rate PKR/USD = rate × 100 paisa when rate is major/major.
     * paisa = cents * (rate_e6 / FX_SCALE)  with integer arithmetic:
     *         = (cents * rate_e6) / FX_SCALE  (half-up)
     */
    public static function usdCentsToPkrPaisa(int $usdCents, int $fxRateE6): int
    {
        if ($usdCents === 0 || $fxRateE6 === 0) {
            return 0;
        }

        $product = $usdCents * $fxRateE6;
        $half = intdiv(self::FX_SCALE, 2);

        return intdiv($product + $half, self::FX_SCALE);
    }

    /**
     * Basis points of amount (10000 bps = 100%).
     */
    public static function applyBps(int $amount, int $bps): int
    {
        if ($amount === 0 || $bps === 0) {
            return 0;
        }

        $product = $amount * $bps;

        return intdiv($product + 5000, 10000);
    }
}
