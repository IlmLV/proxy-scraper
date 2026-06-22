<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Validations\Domains;

use IlmLV\ProxyScraper\Validations\AbstractRequestValidation;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Base class for domain reachability validators.
 *
 * A domain validator probes a single public URL through the proxy and decides
 * whether the response is what that site should return. Concrete validators only
 * declare what is specific to them — the domain {@see self::NAME}, the
 * {@see self::URL} to request (and optionally a non-GET {@see self::METHOD}) —
 * and implement {@see self::validate()}. The request/latency plumbing and
 * construction are inherited from {@see AbstractRequestValidation}.
 */
abstract class AbstractDomainValidation extends AbstractRequestValidation
{
    /**
     * Domain identifier used to key results (e.g. "example.com"). Each concrete
     * validator must override this.
     *
     * @var string
     */
    public const NAME = '';

    /**
     * HTTP method used to probe the domain.
     *
     * @var string
     */
    public const METHOD = 'GET';

    /**
     * URL requested through the proxy. Each concrete validator must override this.
     *
     * @var string
     */
    public const URL = '';

    public function __construct(?HttpClientInterface $client = null)
    {
        parent::__construct(static::METHOD, static::URL, $client);
    }
}
