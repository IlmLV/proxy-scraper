<?php

namespace IlmLV\ProxyScraper\Tests\Unit;

use IlmLV\ProxyScraper\Helper;
use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{
    public function testBenchmarkReturnsCallableResultAndMeasuresLatency(): void
    {
        $latency = null;

        $result = Helper::benchmark($latency, fn () => 'payload');

        $this->assertSame('payload', $result);
        $this->assertIsFloat($latency);
        $this->assertGreaterThanOrEqual(0.0, $latency);
    }
}
