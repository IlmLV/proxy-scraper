<?php

namespace IlmLV\ProxyScraper\Tests\Unit\Validations;

use IlmLV\ProxyScraper\Entities\ResponseError;
use IlmLV\ProxyScraper\Tests\Support\MockClientFactory;
use IlmLV\ProxyScraper\Validations\Domains\ExampleCom;
use IlmLV\ProxyScraper\Validations\DomainsValidation;
use IlmLV\ProxyScraper\Validations\IpValidation;
use IlmLV\ProxyScraper\Validations\IpVersionValidation;
use IlmLV\ProxyScraper\Validations\MethodsValidation;
use IlmLV\ProxyScraper\Validations\ProxyValidation;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Response\MockResponse;

class ProxyValidationTest extends TestCase
{
    public function testFullHappyPathProducesValidResult(): void
    {
        $validation = ProxyValidation::make('http://1.2.3.4:8080', self::happyPathClient())->setDomainValidators([ExampleCom::class])->run();

        $this->assertTrue($validation->valid);
        $this->assertSame('elite', $validation->anonymityLevel);

        $this->assertInstanceOf(IpValidation::class, $validation->ip);
        $this->assertTrue($validation->ip->valid);

        $this->assertInstanceOf(MethodsValidation::class, $validation->http);
        $this->assertInstanceOf(MethodsValidation::class, $validation->https);
        $this->assertTrue($validation->http->get->valid);
        $this->assertTrue($validation->https->get->valid);

        // An HTTP proxy also gets the separate CONNECT-tunnel-to-:80 capability
        // check, probed across all methods.
        $this->assertInstanceOf(MethodsValidation::class, $validation->httpTunnel);
        foreach (['get', 'post', 'put', 'options', 'head', 'delete', 'patch'] as $method) {
            $this->assertTrue($validation->httpTunnel->{$method}->valid, "tunnel {$method} should validate");
        }

        $this->assertInstanceOf(DomainsValidation::class, $validation->domains);
        $this->assertTrue($validation->domains->{'example.com'}->valid);

        $this->assertInstanceOf(IpVersionValidation::class, $validation->ipVersion);
        $this->assertTrue($validation->ipVersion->ipv4->valid);
        $this->assertTrue($validation->ipVersion->ipv6->valid);

        // timestamp is non-deterministic, assert only its type
        $this->assertInstanceOf(\DateTimeInterface::class, $validation->validatedAt);
    }

    public function testHttpsStrictFailsThenInsecureProbeRuns(): void
    {
        // The proxy tunnels TLS, but the certificate fails verification (expired,
        // self-signed, or intercepted). The strict $https check (verify_peer =>
        // true) must fail every method, and that failure must trigger the insecure
        // fallback (verify_peer => false), which then succeeds.
        $client = MockClientFactory::router(function (string $method, string $url, array $options): MockResponse {
            if (str_starts_with($url, 'https://')) {
                if (($options['verify_peer'] ?? false) === true) {
                    // A transport-level cert rejection, as a real client would see.
                    return new MockResponse('', ['error' => 'SSL certificate problem: self-signed certificate']);
                }
                if ($method === 'HEAD') {
                    return new MockResponse('', ['http_code' => 200]);
                }
                return new MockResponse(json_encode(['method' => $method]), ['http_code' => 200]);
            }
            if (str_contains($url, 'whoami')) {
                if (($options['proxy'] ?? null) === false) {
                    return new MockResponse(MockClientFactory::load('Validations/realip.json'));
                }
                if ($method === 'HEAD') {
                    return new MockResponse('', ['http_code' => 200]);
                }
                return new MockResponse(json_encode(['method' => $method]), ['http_code' => 200]);
            }
            if (str_contains($url, 'ipv4.serviss.it') || str_contains($url, 'ipv6.serviss.it')) {
                return new MockResponse(json_encode(['ip' => '1.2.3.4']), ['http_code' => 200]);
            }
            if (str_contains($url, 'ip.serviss.it')) {
                return new MockResponse(MockClientFactory::load('Validations/ip-match.json'));
            }
            return new MockResponse('{}', ['http_code' => 200]);
        });

        $validation = ProxyValidation::make('http://1.2.3.4:8080', $client)->run();

        $this->assertInstanceOf(MethodsValidation::class, $validation->https);
        $this->assertFalse($validation->https->get->valid, 'strict HTTPS must fail on an unverifiable certificate');
        $this->assertNull($validation->https->latency, 'no method should pass the strict check');

        $this->assertInstanceOf(MethodsValidation::class, $validation->httpsInsecure);
        $this->assertTrue($validation->httpsInsecure->get->valid, 'insecure HTTPS probe should succeed when verification is off');
    }

    public function testHttpsStrictPassesSkipsInsecureProbe(): void
    {
        // When the certificate verifies (happy path succeeds regardless of the
        // verify flag), the insecure fallback must not run — httpsInsecure stays null.
        $validation = ProxyValidation::make('http://1.2.3.4:8080', self::happyPathClient())->run();

        $this->assertInstanceOf(MethodsValidation::class, $validation->https);
        $this->assertTrue($validation->https->get->valid);
        $this->assertNull($validation->httpsInsecure, 'insecure probe must not run when strict HTTPS already passed');
    }

    public function testHttpProxyTunnelCheckForcesConnectWhileHttpStaysForward(): void
    {
        if (!defined('CURLOPT_HTTPPROXYTUNNEL')) {
            $this->markTestSkipped('ext-curl not loaded');
        }

        $sawTunnel = false;
        $sawForward = false;
        $client = MockClientFactory::router(function (string $method, string $url, array $options) use (&$sawTunnel, &$sawForward): MockResponse {
            if (str_contains($url, 'whoami') && ($options['proxy'] ?? null) !== false) {
                if (($options['extra']['curl'][CURLOPT_HTTPPROXYTUNNEL] ?? false) === true) {
                    $sawTunnel = true;
                } else {
                    $sawForward = true;
                }
                if ($method === 'HEAD') {
                    return new MockResponse('', ['http_code' => 200]);
                }
                return new MockResponse(json_encode(['method' => $method]), ['http_code' => 200]);
            }
            if (($options['proxy'] ?? null) === false) {
                return new MockResponse(MockClientFactory::load('Validations/realip.json'));
            }
            return new MockResponse(json_encode(['ip' => '1.2.3.4']), ['http_code' => 200]);
        });

        ProxyValidation::make('http://1.2.3.4:8080', $client)->run();

        $this->assertTrue($sawTunnel, 'httpTunnel check must force a CONNECT tunnel');
        $this->assertTrue($sawForward, '$http must stay a forward request');
    }

    public function testSocksProxySkipsSeparateTunnelCheck(): void
    {
        // A SOCKS proxy has a single mode (always a tunnel), so $http already
        // represents it and the separate CONNECT-tunnel probe is not run —
        // httpTunnel stays null while $http still validates.
        $validation = ProxyValidation::make('socks5://1.2.3.4:1080', self::happyPathClient())->run();

        $this->assertTrue($validation->valid);
        $this->assertInstanceOf(MethodsValidation::class, $validation->http);
        $this->assertTrue($validation->http->get->valid);
        $this->assertNull($validation->httpTunnel);
    }

    public function testAnonymityFailureDoesNotAbortSiblingValidations(): void
    {
        // whoami answers the methods probes (which carry the A-IM header) and the
        // real-IP baseline (proxy:false) normally, but serves the anonymity probe
        // a verification wall so only the anonymity check fails.
        $client = MockClientFactory::router(function (string $method, string $url, array $options): MockResponse {
            if (str_contains($url, 'whoami')) {
                if (($options['proxy'] ?? null) === false) {
                    return new MockResponse(MockClientFactory::load('Validations/realip.json'));
                }
                if (isset($options['normalized_headers']['a-im'])) {
                    if ($method === 'HEAD') {
                        return new MockResponse('', ['http_code' => 200]);
                    }
                    return new MockResponse(json_encode(['method' => $method]), ['http_code' => 200]);
                }

                return new MockResponse(MockClientFactory::load('Validations/anonymity-verify.html'));
            }
            if (str_contains($url, 'ipv4.serviss.it') || str_contains($url, 'ipv6.serviss.it')) {
                return new MockResponse(json_encode(['ip' => '1.2.3.4']), ['http_code' => 200]);
            }
            if (str_contains($url, 'ip.serviss.it')) {
                return new MockResponse(MockClientFactory::load('Validations/ip-match.json'));
            }

            return new MockResponse('{}', ['http_code' => 200]);
        });

        $validation = ProxyValidation::make('http://1.2.3.4:8080', $client)->run();

        // Anonymity could not be determined, but that no longer aborts the run.
        $this->assertNull($validation->anonymityLevel);
        $this->assertTrue($validation->valid);

        // The remaining validations still ran and produced results.
        $this->assertInstanceOf(IpValidation::class, $validation->ip);
        $this->assertTrue($validation->ip->valid);
        $this->assertInstanceOf(MethodsValidation::class, $validation->http);
        $this->assertTrue($validation->http->get->valid);
        $this->assertInstanceOf(IpVersionValidation::class, $validation->ipVersion);
        $this->assertTrue($validation->ipVersion->ipv4->valid);
    }

    public function testFailureWhenEndpointsUnreachable(): void
    {
        $client = MockClientFactory::router(fn (string $method, string $url, array $options) => new MockResponse('', ['http_code' => 500]));

        $validation = ProxyValidation::make('http://1.2.3.4:8080', $client)->run();

        $this->assertFalse($validation->valid);
        $this->assertInstanceOf(ResponseError::class, $validation->error);

        // The result-carrying properties must be safe to read in the failure
        // state, not throw "typed property must not be accessed before
        // initialization".
        $this->assertNull($validation->anonymityLevel);
        $this->assertNull($validation->ip);
        $this->assertNull($validation->http);
        $this->assertNull($validation->httpTunnel);
        $this->assertNull($validation->https);
        $this->assertNull($validation->httpsInsecure);
        $this->assertNull($validation->domains);
        $this->assertNull($validation->ipVersion);
    }

    /**
     * Routes the requests ProxyValidation makes to appropriate fixtures,
     * regardless of order:
     *  - whoami + proxy:false  -> the real (direct) IP
     *  - whoami otherwise      -> echo {method}, so anonymity reads "elite" and
     *                             every MethodsValidation request validates
     *  - ip.serviss.it         -> reports the proxy host IP (matches 1.2.3.4)
     *  - ipv4/ipv6.serviss.it  -> reachable egress, reports an IP
     *  - example.com           -> the expected landing page (opt-in domain check)
     */
    private static function happyPathClient(): \Symfony\Component\HttpClient\MockHttpClient
    {
        return MockClientFactory::router(function (string $method, string $url, array $options): MockResponse {
            if (str_contains($url, 'whoami')) {
                if (($options['proxy'] ?? null) === false) {
                    return new MockResponse(MockClientFactory::load('Validations/realip.json'));
                }
                if ($method === 'HEAD') {
                    return new MockResponse('', ['http_code' => 200]);
                }
                return new MockResponse(json_encode(['method' => $method]), ['http_code' => 200]);
            }
            if (str_contains($url, 'ipv4.serviss.it') || str_contains($url, 'ipv6.serviss.it')) {
                return new MockResponse(json_encode(['ip' => '1.2.3.4']), ['http_code' => 200]);
            }
            if (str_contains($url, 'ip.serviss.it')) {
                return new MockResponse(MockClientFactory::load('Validations/ip-match.json'));
            }

            return str_contains($url, 'example.com')
                ? new MockResponse(MockClientFactory::load('Validations/example.html'))
                : new MockResponse('{}', ['http_code' => 200]);
        });
    }
}
