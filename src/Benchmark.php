<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper;

final class Benchmark
{
    /**
     * Run $callback, write its wall-clock duration (seconds) into $latency, and
     * return the callback's result.
     *
     * @template T
     * @param callable(): T $callback
     * @param-out float $latency
     * @return T
     */
    public static function measure(float|null &$latency, callable $callback): mixed
    {
        $startTime = microtime(true);
        $result = $callback();
        $latency = microtime(true) - $startTime;

        return $result;
    }
}
