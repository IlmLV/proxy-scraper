<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Validations;

use IlmLV\ProxyScraper\Entities\Host;
use IlmLV\ProxyScraper\Entities\Proxy;
use IlmLV\ProxyScraper\Entities\RandomUserAgent;
use IlmLV\ProxyScraper\Entities\ResponseError;
use IlmLV\ProxyScraper\Exceptions\InvalidArgumentException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProxyValidation
{
    private Proxy $proxy;
    protected HttpClientInterface $client;

    private string $httpUrl = 'http://whoami.serviss.it/?format=json';
    private string $httpsUrl = 'https://whoami.serviss.it/?format=json';

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
        $this->proxy = is_string($proxy) ? new Proxy($proxy) : $proxy;

        $this->client = $client ?? HttpClient::create([
            'timeout' => 10,
            'verify_peer' => false,
            'verify_host' => false,
            'headers' => [
                'Accept-Language'=>'en-US,en;q=0.5',
                'User-Agent' => new RandomUserAgent,
            ],
            'proxy' => $this->proxy
        ]);

        $this->validate();
    }

    private function validate(): void
    {
        try {
            $this->validatedAt = new \DateTime();
            $this->anonymityLevel = (string) new AnonymityLevelValidation($this->realIp(), $this->client);
            $this->ip = new IpValidation($this->proxy->host, $this->client);
            $this->http = new MethodsValidation($this->httpUrl, $this->client);
            $this->https = new MethodsValidation($this->httpsUrl, $this->client);
            $this->domains = new DomainsValidation($this->client);
            $this->ipVersion = new IpVersionValidation($this->client);
        }
        catch (\Throwable $e) {
            $this->valid = false;
            $this->error = new ResponseError($e);
        }
    }

    private function realIp(): Host
    {
        $response = $this->client->request('GET', $this->httpUrl, [
            'proxy' => false
        ]);

        $body = json_decode($response->getContent(), true);
        $ip = is_array($body) ? ($body['ip'] ?? null) : null;

        if (!is_string($ip)) {
            throw new InvalidArgumentException('Failed to resolve real IP from response');
        }

        return new Host($ip);
    }
}
