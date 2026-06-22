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

}
