<?php

namespace IlmLV\ProxyScraper\Tests\Unit;

use IlmLV\ProxyScraper\Benchmark;
use PHPUnit\Framework\TestCase;

class BenchmarkTest extends TestCase
{
    public function testMeasureReturnsCallableResultAndRecordsLatency(): void
    {
        $latency = null;

        $result = Benchmark::measure($latency, fn () => 'payload');

        $this->assertSame('payload', $result);
        $this->assertIsFloat($latency);
        $this->assertGreaterThanOrEqual(0.0, $latency);
    }
}
