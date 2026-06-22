<?php

declare(strict_types=1);

if (!function_exists('snakeToCamel')) {
    function snakeToCamel(string $input): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $input))));
    }
}

if (!function_exists('kebabToSnake')) {
    function kebabToSnake(string $input): string
    {
        return str_replace('-', '_', $input);
    }
}
