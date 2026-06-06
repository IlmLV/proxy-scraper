<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Scrapers;

/*
 * Backward-compatibility shim for the historical misspelling "JsonListScrapper".
 * Use IlmLV\ProxyScraper\Scrapers\JsonListScraper instead. This alias is loaded
 * on demand by the PSR-4 autoloader and will be removed in the next major.
 */
class_alias(JsonListScraper::class, __NAMESPACE__ . '\\JsonListScrapper');
