<?php

namespace App\Support;

/**
 * Real Google reviews per GLS centre, from config/google_reviews.php
 * (copied by hand from the public Google profiles — no API).
 */
class GoogleReviews
{
    /** @return array<string, array> centres keyed by slug */
    public static function centres(): array
    {
        return config('google_reviews.centres', []);
    }

    public static function centre(string $slug): ?array
    {
        $c = self::centres()[$slug] ?? null;

        return $c ? $c + ['slug' => $slug] : null;
    }

    /**
     * All reviews, interleaved centre by centre (Rabat, Salé, Marrakech, …, then the
     * second review of each centre, …) so every carousel track mixes the cities.
     *
     * @return list<array{name:string,year:int,lang:string,text:string,slug:string,city:string}>
     */
    public static function interleaved(): array
    {
        $out = [];
        $centres = self::centres();
        $max = max(array_map(fn ($c) => count($c['reviews']), $centres ?: [['reviews' => []]]));
        for ($i = 0; $i < $max; $i++) {
            foreach ($centres as $slug => $c) {
                if (isset($c['reviews'][$i])) {
                    $out[] = $c['reviews'][$i] + ['slug' => $slug, 'city' => $c['city']];
                }
            }
        }

        return $out;
    }

    /** Total review count and count-weighted average rating across the six profiles. */
    public static function totals(): array
    {
        $count = 0;
        $sum = 0.0;
        foreach (self::centres() as $c) {
            $count += $c['count'];
            $sum += $c['rating'] * $c['count'];
        }

        return ['count' => $count, 'rating' => $count ? round($sum / $count, 1) : null];
    }

    /** Locale-aware number formatting: 1 379 / 1,379 / 1.379 and 4,9 / 4.9. */
    public static function number(float|int $n, int $decimals = 0): string
    {
        [$dec, $thou] = match (app()->getLocale()) {
            'fr' => [',', "\u{202F}"],
            'de' => [',', '.'],
            'ar' => ['.', ''],
            default => ['.', ','],
        };

        return number_format($n, $decimals, $dec, $thou);
    }
}
