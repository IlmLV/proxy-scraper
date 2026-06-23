<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Validations;

/**
 * Default endpoints the validation subsystem probes through the proxy. 
 * They are centralised here so the URLs live in one place instead of 
 * being duplicated across the individual validations.
 */
final class ValidationEndpoints
{
    /** Echoes the request (headers, method, observed client IP) as JSON. */
    public const WHOAMI_HTTP = 'http://whoami.serviss.it/?format=json';
    public const WHOAMI_HTTPS = 'https://whoami.serviss.it/?format=json';

    /** Reports the caller's IP plus its country/organisation as JSON. */
    public const IP = 'http://ip.serviss.it/?format=json';

    /** A-record-only / AAAA-record-only hosts used to probe egress per address family. */
    public const IPV4 = 'http://ipv4.serviss.it/?format=json';
    public const IPV6 = 'http://ipv6.serviss.it/?format=json';
}
