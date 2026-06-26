<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper;

/**
 * Small string-casing helpers used by option routing and header matching.
 */
final class Str
{
    public static function snakeToCamel(string $input): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $input))));
    }

    public static function kebabToSnake(string $input): string
    {
        return str_replace('-', '_', $input);
    }
}
