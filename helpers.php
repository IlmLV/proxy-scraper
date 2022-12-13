<?php

if (!function_exists('snakeToCamel')) {
    function snakeToCamel($input): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $input))));
    }
}

if (!function_exists('kebabToSnake')) {
    function kebabToSnake($input): string
    {
        return str_replace('-', '_', $input);
    }
}