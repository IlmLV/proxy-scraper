<?php

namespace IlmLV\ProxyScraper\Validations;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class MethodsValidation
{
    private array $requestMethods = [
        'GET',
        'POST',
        'PUT',
        'OPTIONS',
        'HEAD',
        'DELETE',
        'PATCH',
    ];

    public ?float $latency;
    public HeadersValidation $get;
    public HeadersValidation $post;
    public HeadersValidation $put;
    public HeadersValidation $options;
    public HeadersValidation $head;
    public HeadersValidation $delete;
    public HeadersValidation $patch;

    public function __construct(string $url, HttpClientInterface $client = null, array $requestMethods = null)
    {
        if ($requestMethods)
            $this->requestMethods = $requestMethods;

        $latencySum = $latencyCount = null;

        foreach ($this->requestMethods as $method) {
            $this->{strtolower($method)} = new HeadersValidation($method, $url, $client);

            if ($this->{strtolower($method)}->valid) {
                $latencySum += $this->{strtolower($method)}->latency;
                $latencyCount++;
            }
        }
        $this->latency = $latencyCount > 0 ? $latencySum/$latencyCount : null;
    }
}