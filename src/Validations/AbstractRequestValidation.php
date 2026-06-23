<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Validations;

use IlmLV\ProxyScraper\Benchmark;
use IlmLV\ProxyScraper\Entities\ResponseError;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract class AbstractRequestValidation
{
    protected HttpClientInterface $client;

    protected string $method;

    protected string $url;

    public bool $valid = false;

    public ?float $latency = null;

    public ?ResponseError $error = null;

    protected bool $useBenchmark = true;

    public function __construct(string $method, string $url, ?HttpClientInterface $client = null)
    {
        $this->client = $client ?? HttpClient::create();
        $this->method = $method;
        $this->url = $url;
        $this->valid = $this->validate();
    }

    /**
     * Run the validation for this request and report whether the proxy passed.
     */
    abstract public function validate(): bool;

    /**
     * @param array<string, mixed> $options
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    protected function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $request = function () use ($method, $url, $options) {
            $response = $this->client->request($method, $url, $options);
            $response->getStatusCode(); // triggers actual request for benchmark
            return $response;
        };

        return $this->useBenchmark ? Benchmark::measure($this->latency, $request) : $request();
    }

    /**
     * Read the response body, decompressing it when the server applied a
     * Content-Encoding. Some validations set Accept-Encoding themselves (to
     * exercise the proxy's request-header forwarding); when the request sets
     * that header explicitly Symfony's HttpClient leaves the body compressed,
     * so json_decode would fail unless we decode it here first.
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    protected function decodedContent(ResponseInterface $response): string
    {
        $body = $response->getContent();

        $encoding = strtolower($response->getHeaders(false)['content-encoding'][0] ?? '');

        if ($encoding === 'gzip' || $encoding === 'x-gzip' || str_starts_with($body, "\x1f\x8b")) {
            $decoded = @gzdecode($body);
        } elseif ($encoding === 'deflate') {
            // Either zlib-wrapped (RFC 1950) or raw (RFC 1951) deflate.
            $decoded = @gzuncompress($body);
            if ($decoded === false) {
                $decoded = @gzinflate($body);
            }
        } else {
            return $body;
        }

        return $decoded === false ? $body : $decoded;
    }
}
