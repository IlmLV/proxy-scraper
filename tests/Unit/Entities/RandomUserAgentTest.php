<?php

namespace IlmLV\ProxyScraper\Tests\Unit\Entities;

use IlmLV\ProxyScraper\Entities\RandomUserAgent;
use PHPUnit\Framework\TestCase;

class RandomUserAgentTest extends TestCase
{
    public function testReturnsNonEmptyUserAgentString(): void
    {
        $userAgent = (string) new RandomUserAgent();

        $this->assertNotEmpty($userAgent);
        $this->assertStringContainsString('Mozilla', $userAgent);
    }
}
