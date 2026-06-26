<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Scrapers;

/**
 * Shared JSON field-name configuration for the JSON scrapers: which keys in each
 * proxy object hold the host, port and protocol. Override per source as needed.
 */
trait JsonFieldMapping
{
    protected string $hostProperty = 'ip';
    protected string $portProperty = 'port';
    protected string $protocolProperty = 'protocol';
}
