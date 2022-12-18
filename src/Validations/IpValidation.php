<?php

namespace IlmLV\ProxyScraper\Validations;

use IlmLV\ProxyScraper\Entities\Host;
use IlmLV\ProxyScraper\Entities\ResponseError;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class IpValidation extends AbstractRequestValidation
{
    const URL = 'http://ip.serviss.it/?format=json';

    /**
     * @var Host
     */
    private Host $proxyHost;

    /**
     * @var bool
     */
    protected bool $useBenchmark = false;

    /**
     * @var string
     */
    public string $countryIsoCode;

    /**
     * @var string
     */
    public string $organisation;

    /**
     * @var ResponseError
     */
    public ResponseError $error;

    /**
     * @param Host $proxyHost
     * @param HttpClientInterface|null $client
     */
    public function __construct(Host $proxyHost, HttpClientInterface $client = null)
    {
        $this->proxyHost = $proxyHost;

        parent::__construct('GET', self::URL, $client);
    }

    /**
     * @return bool
     */
    public function validate(): bool
    {
        try {
            $response = $this->request('GET', self::URL);
            $body = json_decode($response->getContent());

            $this->countryIsoCode = $body->country->iso_code;
            $this->organisation = $body->organisation;

            return $response->getStatusCode() === 200 && $body->ip === $this->proxyHost->ip;
        }
        catch (\Throwable $e) {
            $this->error = new ResponseError($e);
            return false;
        }
    }
}