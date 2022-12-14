<?php

namespace IlmLV\ProxyScraper\Validators;

use IlmLV\ProxyScraper\Entities\Host;
use IlmLV\ProxyScraper\Entities\Proxy;
use IlmLV\ProxyScraper\Entities\RandomUserAgent;
use IlmLV\ProxyScraper\Entities\ResponseError;
use IlmLV\ProxyScraper\Exceptions\InvalidArgumentException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProxyValidator
{
    private Proxy $proxy;
    protected HttpClientInterface $client;

    private string $httpUrl = 'http://whoami.serviss.it/?format=json';
    private string $httpsUrl = 'https://whoami.serviss.it/?format=json';

    public bool $valid = true;
    public ResponseError $error;

    public string $anonymityLevel;
    public IpValidator $ip;

    public MethodsValidator $http;
    public MethodsValidator $https;
    public DomainsValidator $domains;

    public \DateTimeInterface $validatedAt;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(Proxy|string $proxy, HttpClientInterface $client = null)
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
    }

    public function validate(): self
    {
        try {
            $this->validatedAt = new \DateTime();
            $this->anonymityLevel = new AnonymityLevelValidator($this->realIp(), $this->client);
            $this->ip = new IpValidator($this->proxy->host, $this->client);
            $this->http = new MethodsValidator($this->httpUrl, $this->client);
            $this->https = new MethodsValidator($this->httpsUrl, $this->client);
            $this->domains = new DomainsValidator($this->client);
        }
        catch (\Throwable $e) {
            $this->valid = false;
            $this->error = new ResponseError($e);
        }

        return $this;
    }

    private function realIp(): Host
    {
        $response = $this->client->request('GET', $this->httpUrl, [
            'proxy' => false
        ]);

        return new Host(json_decode($response->getContent())->ip);
    }
}
