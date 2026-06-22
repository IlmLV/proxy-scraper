# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).
While the package is pre-1.0 (`0.x`), any release may contain breaking changes.

## [Unreleased]

### Changed (breaking — output/shape)

- `ResponseError::$line` is now an `int` instead of a `string`. JSON output for any
  captured error changes from `"line": "56"` to `"line": 56`.
- The per-request validations (`IpValidation`, `HeadersValidation`, `EgressValidation`)
  no longer redeclare `error`; it lives on `AbstractRequestValidation` as
  `?ResponseError` defaulting to `null`. Successful checks now serialise an explicit
  `"error": null` (previously the field was omitted) and reading `->error` on a
  successful check returns `null` instead of throwing.
- `Proxy` (`protocol`, `host`, `port`, `username`, `password`) and `Host::$host`
  properties are now `readonly`. Reassigning them after construction is no longer
  allowed.
- `ResponseError` (`message`, `file`, `line`) properties are now `readonly`, and a
  `type` field (the captured throwable's class) was added. JSON output for a captured
  error gains a leading `"type": "<Fully\\Qualified\\ExceptionClass>"`.

### Changed (breaking — API)

- `Host::$ip` (a public property) has been **removed** in favour of `Host::ip(): ?string`.
  Resolution is now lazy — no DNS lookup happens during construction — and returns
  `null` when a hostname cannot be resolved instead of silently echoing the input back.
- The `Helper` class is renamed to `Benchmark` and its `benchmark()` method to
  `measure()`. Use `Benchmark::measure($latency, $callback)`.
- The global `snakeToCamel()` / `kebabToSnake()` functions (and the `helpers.php`
  autoload entry) are removed. Use the static `Str::snakeToCamel()` /
  `Str::kebabToSnake()` instead.

### Added

- `Port::$value` (`int`) and `Protocol::$value` (`string`) expose the underlying
  value directly, complementing `__toString()` and matching `Host`'s public surface.

### Fixed (behaviour)

- Result properties on the request validations now read as `null` on the failure
  path instead of throwing "must not be accessed before initialization":
  `AbstractRequestValidation::$latency` and `IpValidation::$countryIsoCode` /
  `$organisation` default to `null`.
- `TextListScraper` and `TableListScraper` now build their request URL through
  `getUrl()`, so per-source `scraperConfig` query parameters (and `sprintf` URL
  templates) apply to them as they already did for the JSON scrapers.
- `Proxy` string parsing now accepts bracketed IPv6 hosts (`http://[::1]:8080`) and
  passwords containing `:` or `@`; `__toString()` re-brackets IPv6 hosts so the
  result round-trips.
- Re-running `LoadProxies` (`all()`/`scheduled()`/`only()` more than once) no longer
  accumulates duplicate proxies — each scraper's result replaces its previous one.
- A failed proxy anonymity check no longer aborts the rest of `ProxyValidation`.
  Previously a non-determinable anonymity level threw and left `ip`, `http`, `https`,
  `domains` and `ipVersion` unset; now `anonymityLevel` is `null` and the remaining
  checks still run. (Resolving the proxy's baseline real IP remains a hard prerequisite.)
- `JsonListScraper` now skips a single malformed list entry instead of throwing and
  discarding every proxy from that source — consistent with the text/table scrapers
  and `GeonodeProxyList`. It still raises `ScraperException` when the list container
  itself cannot be located.
- `MethodsValidation` no longer assigns results to fixed typed properties via dynamic
  names. A custom `$requestMethods` list no longer triggers PHP 8.2 dynamic-property
  deprecations or leaves declared properties uninitialised; results are stored in a
  keyed map exposed read-only as `->get`, `->post`, … and the JSON shape is unchanged.

### Internal

- `Proxy` string parsing is extracted from the constructor into a dedicated parser.
- `ProxyScraper` base class is now `abstract` and exposes a shared `fetch()` helper;
  the four scrapers no longer duplicate the GET-and-wrap-errors boilerplate.
- The JSON scrapers' field-name configuration (`$hostProperty` etc.) moved to a
  `JsonFieldMapping` trait, and each scraper base now documents its config contract.
- Added [PHP-CS-Fixer](https://cs.symfony.com/) (`composer cs` / `composer cs:fix`)
  with a gating CI job, and swept redundant docblocks across the codebase.
