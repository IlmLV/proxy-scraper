<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Validations;

/**
 * Shared contract for every validation: construction performs no I/O, and run()
 * executes the check(s), populates the result properties, and returns $this.
 * Implemented by the per-request validations (via AbstractRequestValidation) and
 * by the aggregators (ProxyValidation, MethodsValidation, DomainsValidation,
 * IpVersionValidation).
 */
interface ValidationInterface
{
    public function run(): self;
}
