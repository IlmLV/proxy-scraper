<?php

namespace IlmLV\ProxyScraper\Tests\Support;

use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * Helpers for building Symfony MockHttpClient instances from fixture files,
 * so scraper/validation tests run fully offline and deterministically.
 */
final class MockClientFactory
{
    public static function fixturePath(string $name): string
    {
        return __DIR__ . '/../Fixtures/' . $name;
    }

    public static function load(string $name): string
    {
        $path = self::fixturePath($name);
        if (!is_file($path)) {
            throw new \RuntimeException('Fixture not found: ' . $path);
        }
        return file_get_contents($path);
    }

    /** Single response from a raw body string. */
    public static function fromString(string $body, int $code = 200): MockHttpClient
    {
        return new MockHttpClient(new MockResponse($body, ['http_code' => $code]));
    }

    /** Single response loaded from a fixture file (path relative to tests/Fixtures). */
    public static function fromFixture(string $name, int $code = 200): MockHttpClient
    {
        return self::fromString(self::load($name), $code);
    }

    /** Ordered sequence of fixture-backed responses (consumed in request order). */
    public static function sequence(array $names): MockHttpClient
    {
        $responses = [];
        foreach ($names as $name) {
            $responses[] = new MockResponse(self::load($name), ['http_code' => 200]);
        }
        return new MockHttpClient($responses);
    }

    /**
     * URL/method-routing client. The resolver receives ($method, $url, $options)
     * and must return a MockResponse. Useful for multi-request flows
     * (e.g. ProxyValidation) where request order is awkward to predict.
     */
    public static function router(callable $resolver): MockHttpClient
    {
        return new MockHttpClient(static function (string $method, string $url, array $options) use ($resolver): MockResponse {
            return $resolver($method, $url, $options);
        });
    }
}
