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
        // Private members are reflection-accessible without setAccessible() since PHP 8.1.
        $property = $reflection->getProperty('scrapers');

        return $property->getValue($reflection->newInstanceWithoutConstructor());
    }
}
