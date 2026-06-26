<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Validations;

use IlmLV\ProxyScraper\Arr;
use IlmLV\ProxyScraper\Entities\Host;
use IlmLV\ProxyScraper\Entities\ResponseError;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class IpValidation extends AbstractRequestValidation
{
    public const URL = ValidationEndpoints::IP;

    private Host $proxyHost;

    protected bool $useBenchmark = false;

    public ?string $countryIsoCode = null;

    public ?string $organisation = null;

    public function __construct(Host $proxyHost, ?HttpClientInterface $client = null)
    {
        $this->proxyHost = $proxyHost;

        parent::__construct('GET', self::URL, $client);
    }

    public static function make(Host $proxyHost, ?HttpClientInterface $client = null): self
    {
        return new self($proxyHost, $client);
    }

    public function validate(): bool
    {
        try {
            $response = $this->request('GET', self::URL);
            $body = json_decode($response->getContent(), true);

            $countryIsoCode = Arr::get($body, 'country.iso_code');
            $organisation = Arr::get($body, 'organisation');
            $ip = Arr::get($body, 'ip');

            if (!is_string($countryIsoCode) || !is_string($organisation)) {
                return false;
            }

            $this->countryIsoCode = $countryIsoCode;
            $this->organisation = $organisation;

            return $response->getStatusCode() === 200 && $ip === $this->proxyHost->ip();
        } catch (\Throwable $e) {
            $this->error = new ResponseError($e);
            return false;
        }
    }
}
