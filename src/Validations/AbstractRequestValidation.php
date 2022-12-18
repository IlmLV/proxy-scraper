<?php

namespace IlmLV\ProxyScraper\Validations;

use IlmLV\ProxyScraper\Entities\ResponseError;
use IlmLV\ProxyScraper\Helper;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract class AbstractRequestValidation
{
    /**
     * @var HttpClientInterface
     */
    protected HttpClientInterface $client;

    /**
     * @var string
     */
    protected string $method;

    /**
     * @var string
     */
    protected string $url;

    /**
     * @var bool
     */
    public bool $valid;

    /**
     * @var float|null
     */
    public float|null $latency;

    /**
     * @var ResponseError
     */
    public ResponseError $error;

    /**
     * @var bool
     */
    protected bool $useBenchmark = true;

    /**
     * @param string $method
     * @param string $url
     * @param HttpClientInterface|null $client
     */
    public function __construct(string $method, string $url, HttpClientInterface $client = null)
    {
        $this->client = $client ?? HttpClient::create();
        $this->method = $method;
        $this->url = $url;
        $this->valid = $this->validate();
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $options
     * @return ResponseInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    protected function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $request = function() use ($method, $url, $options) {
            $response = $this->client->request($method, $url, $options);
            $response->getStatusCode(); // triggers actual request for benchmark
            return $response;
        };

        return $this->useBenchmark ? Helper::benchmark($this->latency, $request) : $request();
    }

}