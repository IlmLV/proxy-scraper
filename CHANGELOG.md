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
- `Proxy` (`protocol`, `host`, `port`, `username`, `password`) and `Host`
  (`host`, `ip`) properties are now `readonly`. Reassigning them after construction
  is no longer allowed.

### Fixed (behaviour)

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

- `Host` no longer performs a `gethostbyname()` lookup for IP literals; resolution
  happens only for genuine hostnames.
- `Proxy` string parsing is extracted from the constructor into a dedicated parser.
- `ProxyScraper` base class is now `abstract`.
- Added [PHP-CS-Fixer](https://cs.symfony.com/) (`composer cs` / `composer cs:fix`)
  with a gating CI job, and swept redundant docblocks across the codebase.
