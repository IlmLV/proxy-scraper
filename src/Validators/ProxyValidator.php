<?php

namespace IlmLV\ProxyScraper\Validators;

use IlmLV\ProxyScraper\Entities\Host;
use IlmLV\ProxyScraper\Entities\Proxy;
use IlmLV\ProxyScraper\Entities\ResponseError;
use IlmLV\ProxyScraper\Exceptions\InvalidArgumentException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProxyValidator
{
    /**
     * @var Proxy
     */
    private Proxy $proxy;

    /**
     * @var HttpClientInterface
     */
    protected HttpClientInterface $client;

    private string $whoamiUrl = 'http://whoami.serviss.it/?format=json';

    public bool $valid = true;
    public ResponseError $error;

    public string $anonymityLevel;
    public IpValidator $ip;

    public HeadersValidator $https;
    public MethodsValidator $methods;
    public DomainsValidator $domains;

    public array $latency;

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
                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64; rv:102.0) Gecko/20100101 Firefox/102.0',
            ],
            'proxy' => $this->proxy
        ]);
    }

    /**
     * @return $this
     */
    public function validate(): self
    {
        try {
            $this->anonymityLevel = new AnonymityLevelValidator($this->realIp(), $this->client);
            $this->ip = new IpValidator($this->proxy->host, $this->client);
            $this->https = new HeadersValidator('GET', str_replace('http://', 'https://', $this->whoamiUrl), $this->client);
            $this->methods = new MethodsValidator($this->whoamiUrl, $this->client);
            $this->domains = new DomainsValidator($this->client);

            $this->latency = [];
            $this->latency['http'] = $this->averageLatency('http');
            $this->latency['https'] = $this->averageLatency('https');
            $this->validatedAt = new \DateTime();
        }
        catch (\Throwable $e) {
            $this->valid = false;
            $this->error = new ResponseError($e);
        }

        return $this;
    }

    private function realIp(): Host
    {
        $response = $this->client->request('GET', $this->whoamiUrl, [
            'proxy' => false
        ]);

        return new Host(json_decode($response->getContent())->ip);
    }

    /**
     * @return float
     */
    private function averageLatency($protocol = 'http'): ?float
    {
        $sum = $c = null;

        if ($protocol == 'http') {
            foreach ($this->methods as $req) {
                if ($req->valid) {
                    $sum += $req->latency;
                    $c++;
                }
            }
        }
        elseif ($protocol == 'https') {
            if ($this->https->valid) {
                $sum += $this->https->latency;
                $c++;
            }
        }
        return $c > 0 ? $sum/$c : null;
    }
}