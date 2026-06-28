<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Validations;

use IlmLV\ProxyScraper\Arr;
use IlmLV\ProxyScraper\Entities\Host;
use IlmLV\ProxyScraper\Entities\Protocol;
use IlmLV\ProxyScraper\Entities\Proxy;
use IlmLV\ProxyScraper\Entities\RandomUserAgent;
use IlmLV\ProxyScraper\Entities\ResponseError;
use IlmLV\ProxyScraper\Exceptions\InvalidArgumentException;
use IlmLV\ProxyScraper\Validations\Domains\AbstractDomainValidation;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProxyValidation implements ValidationInterface
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
    public ?MethodsValidation $httpTunnel = null;
    public ?MethodsValidation $https = null;
    public ?MethodsValidation $httpsInsecure = null;
    public ?DomainsValidation $domains = null;
    public ?IpVersionValidation $ipVersion = null;

    public \DateTimeInterface $validatedAt;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(Proxy|string $proxy, ?HttpClientInterface $client = null)
    {
        $this->proxy = is_string($proxy) ? Proxy::fromString($proxy) : $proxy;

        // TLS verification is on by default; the two HTTPS probes in run() set it
        // explicitly per request, so this only governs the opt-in domain checks.
        $this->client = $client ?? HttpClient::create([
            'timeout' => 10,
            'verify_peer' => true,
            'verify_host' => true,
            'headers' => [
                'Accept-Language' => 'en-US,en;q=0.5',
                'User-Agent' => RandomUserAgent::random(),
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
            // $http is the proxy's default HTTP behavior, via the forward
            // client: classic forward proxying for an HTTP proxy, the socks
            // tunnel for a SOCKS proxy. ($https is the CONNECT-to-:443 check —
            // transport-driven by the URL scheme.)
            $this->http = MethodsValidation::make($this->httpUrl, $this->client)->run();

            // Each HTTPS probe sets TLS verification explicitly, independent of the
            // injected client. $https verifies the certificate (peer chain +
            // hostname); when it fails every method (latency === null) $httpsInsecure
            // retries without verification — a pass means TLS tunnels behind an
            // untrusted cert.
            $strictClient = $this->client->withOptions(['verify_peer' => true, 'verify_host' => true]);
            $this->https = MethodsValidation::make($this->httpsUrl, $strictClient)->run();

            if ($this->https->latency === null) {
                $insecureClient = $this->client->withOptions(['verify_peer' => false, 'verify_host' => false]);
                $this->httpsInsecure = MethodsValidation::make($this->httpsUrl, $insecureClient)->run();
            }

            // Separate CONNECT-tunnel-to-:80 capability — how a chained proxy /
            // forward-proxy gateway reaches the exit. Only HTTP proxies need this
            // distinct check, because forward and CONNECT differ for them (many
            // forward :80 yet refuse CONNECT to :80).
            if ($this->proxy->protocol === Protocol::Http && defined('CURLOPT_HTTPPROXYTUNNEL')) {
                $tunnelClient = $this->client->withOptions(['extra' => ['curl' => [CURLOPT_HTTPPROXYTUNNEL => true]]]);
                $this->httpTunnel = MethodsValidation::make($this->httpUrl, $tunnelClient)->run();
            }
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
        $ip = Arr::get($body, 'ip');

        if (!is_string($ip)) {
            throw new InvalidArgumentException('Failed to resolve real IP from response');
        }

        return new Host($ip);
    }
}
