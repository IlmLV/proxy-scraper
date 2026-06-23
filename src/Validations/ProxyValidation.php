<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Validations;

use IlmLV\ProxyScraper\Entities\Host;
use IlmLV\ProxyScraper\Entities\Proxy;
use IlmLV\ProxyScraper\Entities\RandomUserAgent;
use IlmLV\ProxyScraper\Entities\ResponseError;
use IlmLV\ProxyScraper\Exceptions\InvalidArgumentException;
use IlmLV\ProxyScraper\Validations\Domains\AbstractDomainValidation;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProxyValidation
{
    private Proxy $proxy;
    protected HttpClientInterface $client;

    /** @var array<class-string<AbstractDomainValidation>> */
    private array $domainValidators = [];

    private string $httpUrl = ValidationEndpoints::WHOAMI_HTTP;
    private string $httpsUrl = ValidationEndpoints::WHOAMI_HTTPS;

    public bool $valid = true;
    public ?ResponseError $error = null;

    public ?string $anonymityLevel = null;
    public ?IpValidation $ip = null;

    public ?MethodsValidation $http = null;
    public ?MethodsValidation $https = null;
    public ?DomainsValidation $domains = null;
    public ?IpVersionValidation $ipVersion = null;

    public \DateTimeInterface $validatedAt;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(Proxy|string $proxy, ?HttpClientInterface $client = null)
    {
        $this->proxy = is_string($proxy) ? Proxy::fromString($proxy) : $proxy;

        $this->client = $client ?? HttpClient::create([
            'timeout' => 10,
            'verify_peer' => false,
            'verify_host' => false,
            'headers' => [
                'Accept-Language' => 'en-US,en;q=0.5',
                'User-Agent' => new RandomUserAgent(),
            ],
            'proxy' => $this->proxy,
        ]);
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function make(Proxy|string $proxy, ?HttpClientInterface $client = null): self
    {
        return new self($proxy, $client);
    }

    /**
     * Opt-in domain validators to run (none by default). Each must extend
     * AbstractDomainValidation (see Domains\ExampleCom for the template); pass
     * e.g. [ExampleCom::class]. Set before run().
     *
     * @param array<class-string<AbstractDomainValidation>> $domainValidators
     */
    public function setDomainValidators(array $domainValidators): self
    {
        $this->domainValidators = $domainValidators;

        return $this;
    }

    /**
     * Run every check against the proxy, populating this object's result
     * properties ($anonymityLevel, $ip, $http, …), and return $this.
     * Construction performs no I/O — call run() to execute the validation.
     */
    public function run(): self
    {
        try {
            $this->validatedAt = new \DateTime();
            $this->anonymityLevel = AnonymityLevelValidation::make($this->realIp(), $this->client)->run()->anonymityLevel;
            $this->ip = IpValidation::make($this->proxy->host, $this->client)->run();
            $this->http = MethodsValidation::make($this->httpUrl, $this->client)->run();
            $this->https = MethodsValidation::make($this->httpsUrl, $this->client)->run();
            $this->domains = DomainsValidation::make($this->client)->setValidators($this->domainValidators)->run();
            $this->ipVersion = IpVersionValidation::make($this->client)->run();
        } catch (\Throwable $e) {
            $this->valid = false;
            $this->error = new ResponseError($e);
        }

        return $this;
    }

    private function realIp(): Host
    {
        $response = $this->client->request('GET', $this->httpUrl, [
            'proxy' => false,
        ]);

        $body = json_decode($response->getContent(), true);
        $ip = is_array($body) ? ($body['ip'] ?? null) : null;

        if (!is_string($ip)) {
            throw new InvalidArgumentException('Failed to resolve real IP from response');
        }

        return new Host($ip);
    }
}
