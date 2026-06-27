<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Validations;

/**
 * Read accessors shared by the validation aggregators that expose their results
 * keyed by string ({@see MethodsValidation} by HTTP method, {@see DomainsValidation}
 * by domain name). The backing map is supplied by {@see resultMap()}; results are
 * built when run() executes. Keeping these accessors in one place guarantees both
 * aggregators behave identically and avoids dynamic-property deprecations.
 */
trait KeyedResultMap
{
    /**
     * @return array<string, object>
     */
    abstract protected function resultMap(): array;

    public function __get(string $name): ?object
    {
        return $this->resultMap()[$name] ?? null;
    }

    public function __isset(string $name): bool
    {
        return isset($this->resultMap()[$name]);
    }
}
