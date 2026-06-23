<?php

namespace IlmLV\ProxyScraper\Tests\Support;

use Generator;
use IlmLV\ProxyScraper\Entities\Proxy;
use IlmLV\ProxyScraper\ProxyScraper;

/**
 * Exposes ProxyScraper's protected helpers so the base behaviour
 * (getUrl/makeProxy/option routing) can be asserted directly.
 */
class StubScraper extends ProxyScraper
{
    protected string $url = 'https://example.test/api/%s';

    /** Set through the set_* option-routing mechanism in loadOptions(). */
    public ?string $fooBar = null;

    /** @return Generator<int, Proxy> */
    public function get(): Generator
    {
        yield from [];
    }

    public function setFooBar($value): void
    {
        $this->fooBar = $value;
    }

    public function buildUrl(string ...$values): string
    {
        return $this->getUrl(...$values);
    }

    public function build($ip, $port, $protocol): Proxy
    {
        return $this->makeProxy($ip, $port, $protocol);
    }

    public function options(): array
    {
        return $this->options;
    }
}
