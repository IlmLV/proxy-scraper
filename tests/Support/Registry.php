<?php

namespace IlmLV\ProxyScraper\Tests\Support;

use IlmLV\ProxyScraper\LoadProxies;

/**
 * Reads the private $scrapers registry from LoadProxies so tests can iterate
 * over every Source that ships enabled by default.
 */
final class Registry
{
    /** @return string[] registered Source class names */
    public static function scrapers(): array
    {
        $reflection = new \ReflectionClass(LoadProxies::class);
        $instance = $reflection->newInstanceWithoutConstructor();

        return (\Closure::bind(static fn (LoadProxies $lp): array => $lp->scrapers, null, LoadProxies::class))($instance);
    }
}
