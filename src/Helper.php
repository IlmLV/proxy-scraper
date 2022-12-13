<?php

namespace IlmLV\ProxyScraper;

class Helper
{
    /**
     * @param float|null $latency
     * @param callable $request
     * @return mixed
     */
    public static function benchmark(float|null &$latency, callable $request): mixed
    {
        $startTime = microtime(true);
        $response = call_user_func($request);
        $latency = microtime(true) - $startTime;

        return $response;
    }
}