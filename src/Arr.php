<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper;

/**
 * Small array-access helpers for safely reading values out of decoded JSON
 * payloads of unknown shape (json_decode(..., true) returns mixed).
 */
final class Arr
{
    /**
     * Read a value from a (possibly non-array) input by dot-notation key path,
     * returning $default when the input is not an array, a segment is missing, or
     * a traversed value is not an array — e.g. Arr::get($body, 'country.iso_code').
     */
    public static function get(mixed $array, string $key, mixed $default = null): mixed
    {
        if (!is_array($array)) {
            return $default;
        }

        $current = $array;
        foreach (explode('.', $key) as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return $default;
            }
            $current = $current[$segment];
        }

        return $current;
    }
}
