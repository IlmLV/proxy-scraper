<?php

namespace IlmLV\ProxyScraper\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    #[DataProvider('snakeToCamelProvider')]
    public function testSnakeToCamel(string $input, string $expected): void
    {
        $this->assertSame($expected, snakeToCamel($input));
    }

    public static function snakeToCamelProvider(): array
    {
        return [
            ['set_foo_bar', 'setFooBar'],
            ['set_protocol', 'setProtocol'],
            ['already', 'already'],
        ];
    }

    #[DataProvider('kebabToSnakeProvider')]
    public function testKebabToSnake(string $input, string $expected): void
    {
        $this->assertSame($expected, kebabToSnake($input));
    }

    public static function kebabToSnakeProvider(): array
    {
        return [
            ['x-real-ip', 'x_real_ip'],
            ['accept-language', 'accept_language'],
            ['nochange', 'nochange'],
        ];
    }
}
