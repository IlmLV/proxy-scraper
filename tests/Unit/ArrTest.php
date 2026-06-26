<?php

namespace IlmLV\ProxyScraper\Tests\Unit;

use IlmLV\ProxyScraper\Arr;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ArrTest extends TestCase
{
    #[DataProvider('getProvider')]
    public function testGet(mixed $array, string $key, mixed $default, mixed $expected): void
    {
        $this->assertSame($expected, Arr::get($array, $key, $default));
    }

    public static function getProvider(): array
    {
        return [
            'top-level key present' => [['ip' => '1.2.3.4'], 'ip', null, '1.2.3.4'],
            'nested dot path' => [['country' => ['iso_code' => 'US']], 'country.iso_code', null, 'US'],
            'numeric segment in dot path' => [
                ['data' => ['items' => [['date' => '2024-01-01']]]],
                'data.items.0.date',
                null,
                '2024-01-01',
            ],
            'missing top-level key returns default' => [['a' => 1], 'b', null, null],
            'missing nested key returns default' => [['country' => []], 'country.iso_code', null, null],
            'intermediate scalar returns default' => [['country' => 'US'], 'country.iso_code', null, null],
            'deep miss past a scalar middle segment' => [
                ['data' => ['items' => 'x']],
                'data.items.0.date',
                null,
                null,
            ],
            'non-array string input returns default' => ['not-an-array', 'k', null, null],
            'non-array null input returns default' => [null, 'k', null, null],
            'custom default on missing key' => [[], 'missing', 'fallback', 'fallback'],
            'custom default on non-array input' => [null, 'k', 'fallback', 'fallback'],
            'present null value returns null not default' => [['k' => null], 'k', 'fallback', null],
            'integer value returned as-is' => [['port' => 8080], 'port', null, 8080],
        ];
    }
}
