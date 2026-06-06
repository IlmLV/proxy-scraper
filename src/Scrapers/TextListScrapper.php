<?php

declare(strict_types=1);

namespace IlmLV\ProxyScraper\Scrapers;

/*
 * Backward-compatibility shim for the historical misspelling "TextListScrapper".
 * Use IlmLV\ProxyScraper\Scrapers\TextListScraper instead. This alias is loaded
 * on demand by the PSR-4 autoloader and will be removed in the next major.
 */
class_alias(TextListScraper::class, __NAMESPACE__ . '\\TextListScrapper');
