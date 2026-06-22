<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper;

class Helper
{
    /**
     * @template T
     * @param callable(): T $request
     * @return T
     */
    public static function benchmark(float|null &$latency, callable $request): mixed
    {
        $startTime = microtime(true);
        $response = call_user_func($request);
        $latency = microtime(true) - $startTime;

        return $response;
    }
}
