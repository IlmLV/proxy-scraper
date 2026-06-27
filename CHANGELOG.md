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

- The validation classes are now built with a static `make()` factory, configured via
  fluent `set*()` methods, and executed by an explicit `run()` method; construction
  performs no I/O. Optional behavioural configuration moved out of constructors into
  setters: `ProxyValidation::setDomainValidators()`, `MethodsValidation::setRequestMethods()`,
  `IpVersionValidation::setIpv4Url()`/`setIpv6Url()`, `DomainsValidation::setValidators()`
  (the HTTP client stays a `make()` argument). Affects `ProxyValidation`,
  `MethodsValidation`, `IpVersionValidation`, `DomainsValidation`, `AnonymityLevelValidation`,
  and the per-request validations (`HeadersValidation`, `IpValidation`, `EgressValidation`,
  `Domains\*`). For example, `new ProxyValidation($proxy, null, [ExampleCom::class])` becomes
  `ProxyValidation::make($proxy)->setDomainValidators([ExampleCom::class])->run()`. The
  public constructors remain available.
- `Protocol` is now a string-backed **enum** (`Protocol::Http`, `Https`, `Socks4`,
  `Socks5`) instead of a value-object class. Build one with `Protocol::fromString($s)`
  (throws `InvalidArgumentException` on an unknown value) or `Protocol::tryFrom($s)`,
  and read the string via `->value`. `(string) $protocol` no longer works — PHP enums
  are not `Stringable`. The `Protocol::ALLOWED_PROTOCOLS` constant is removed; use
  `Protocol::cases()`.
- `Proxy`'s constructor now accepts **only value objects**:
  `new Proxy(Protocol $protocol, Host $host, Port $port, ?string $username, ?string $password)`.
  Parse a `"protocol://[user:pass@]host:port"` string with the new
  `Proxy::fromString(string): self` named constructor instead of `new Proxy('http://…')`.
- `LoadProxies::run()` is now `private` — it is an internal step. Run scrapers via
  `all()`, `scheduled()`, or `only()` / `add()->all()`.
- `Host::$ip` (a public property) has been **removed** in favour of `Host::ip(): ?string`.
  Resolution is now lazy — no DNS lookup happens during construction — and returns
  `null` when a hostname cannot be resolved instead of silently echoing the input back.
- The `Helper` class is renamed to `Benchmark` and its `benchmark()` method to
  `measure()`. Use `Benchmark::measure($latency, $callback)`.
- The global `snakeToCamel()` / `kebabToSnake()` functions (and the `helpers.php`
  autoload entry) are removed. Use the static `Str::snakeToCamel()` /
  `Str::kebabToSnake()` instead.
- `LoadProxies::init()` is renamed to `LoadProxies::make()` for consistency with the
  validation factories. Replace `LoadProxies::init(...)` with `LoadProxies::make(...)`.
- `AnonymityLevelValidation` now extends `AbstractRequestValidation`, gaining the
  family's public `$valid` (bool), `$latency` (`?float`) and `$error`
  (`?ResponseError`) plus a `validate(): bool`, and its result is now measured for
  latency. Its throwing `__toString()` is **removed** — casting the object to a
  string no longer works; read `->anonymityLevel` instead. `$valid` is `true` only
  when the proxy hides the real IP (`elite`/`anonymous`); an `exposed` result or a
  failed probe is `false`. `ProxyValidation`'s JSON output is unchanged — it still
  exposes `anonymityLevel` as the same plain string.
- `RandomUserAgent`'s magic `__toString()` is replaced by a static
  `RandomUserAgent::random(): string`. `(string) new RandomUserAgent()` no longer
  works; use `RandomUserAgent::random()`.
- **Source classes renamed, and the multi-protocol families consolidated.** Each
  provider is now a single class named `<Provider>[Variant]` after its identity
  (domain or GitHub owner/repo), dropping protocol suffixes and the
  `ProxyList`/`OpenProxyList`/`FreshProxyList` filler. The six multi-protocol families
  collapse to one class each that emits all their protocols (via the new
  `ProxyScraper::$protocols` map):
  `AliilaproProxyList{Http,Socks4,Socks5}` → `Aliilapro`;
  `ShiftyTRProxyList{Http,Https,Socks4,Socks5}` → `ShiftyTR`;
  `VakhovFreshProxyList{Http,Https,Socks4,Socks5}` → `Vakhov`;
  `ProxyScrapeCom{Http,Socks4,Socks5}` → `ProxyScrapeCom`;
  `RoosterkidOpenProxyList{Https,Socks4,Socks5}` → `Roosterkid`;
  `TheSpeedXProxyList{Http,Socks4,Socks5}` → `TheSpeedX`.
  Standalone sources renamed: `MonosansProxyListHttp` → `Monosans`,
  `ClarketmProxyList` → `Clarketm`, `Mmpx12ProxyList` → `Mmpx12`,
  `HookzofSocks5List` → `Hookzof`, `ProxyListPlusHttp` → `ProxyListPlus`,
  `ProxiflyProxyList` → `Proxifly`, `GeonodeProxyList` → `Geonode`,
  `SpysMeProxyList` → `SpysMe`. Update any `Sources\*::class` references passed to
  `only()`/`add()` and any `scraperConfig` keys. Consequently `stats()` and `errors()`
  now report one key per provider — a consolidated source's `stats()` value carries all
  its protocol counts (e.g. `['http' => N, 'socks4' => M, 'socks5' => K]`).

### Changed (non-breaking)

- `ProxyScraper` now declares `abstract get(): Generator` and implements
  `ScraperInterface` directly. The bundled scraper base classes and custom sources
  that extend one of them no longer need to repeat `implements ScraperInterface`
  (existing `implements` declarations still compile). `ScraperInterface` remains the
  public contract to type-hint against.
- `HeadersValidation`'s probe header set is now a `private const` instead of a
  per-instance property (no behavioural or output change).

### Added

- `ProxyValidation::$httpTunnel` (`?MethodsValidation`) — a separate **CONNECT-tunnel-to-:80**
  capability check, distinct from `$http` (forward proxying). It reflects how a chained
  proxy / forward-proxy gateway reaches an exit (tunnel, then request), which many HTTP
  proxies treat differently from forward proxying (some forward `:80` yet refuse `CONNECT`
  to it). Run for `Protocol::Http` proxies only — SOCKS always tunnel, so `$http` already
  covers them and `$httpTunnel` stays `null`. Implemented as the same client reconfigured
  via `withOptions()` to force `CURLOPT_HTTPPROXYTUNNEL`; the validation classes themselves
  stay transport-agnostic. JSON output gains an `httpTunnel` object alongside `http`/`https`.
- `Arr::get()` — a small dot-notation accessor for safely reading values out of
  decoded JSON of unknown shape (e.g. `Arr::get($body, 'country.iso_code')`),
  replacing the repeated `is_array(...) ? (...['k'] ?? null) : null` pattern.
- `Validations\ValidationInterface` (`run(): self`) is implemented by the per-request
  validations (via `AbstractRequestValidation`) and the aggregators (`ProxyValidation`,
  `MethodsValidation`, `DomainsValidation`, `IpVersionValidation`), unifying the
  family's contract.

- `ScrapedProxyList::unique()` and `LoadProxies::unique()` return every scraped proxy
  flattened across all sources with exact duplicates removed (sources overlap heavily).
  `get()` still returns every occurrence and remains what `stats()` counts.
- `Port::$value` (`int`) exposes the underlying value directly, complementing
  `__toString()` and matching `Host`'s public surface. (`Protocol`'s value is its enum
  backing value, `Protocol::Http->value` — see the breaking-API note above.)
- `ProxyScraper::$protocols` — a `protocol => URL` map, available on **every** scraper
  and honoured by all four format bases (text / table / JSON list / JSON object, via the
  new `Scrapers\MultiProtocolFetch` trait), letting one source publish several
  per-protocol endpoints from the same provider. Each URL is fetched with the configured
  options applied and its body parsed with that protocol forced (overriding the
  per-row/per-field protocol); a single dead endpoint is skipped rather than aborting the
  source. Backs the consolidated multi-protocol text sources (see the breaking-API note);
  the table/JSON bases accept it too even though no bundled source uses it yet.
- `IpVersionValidation` now implements `JsonSerializable`, emitting an explicit
  `{"ipv4": ..., "ipv6": ...}` shape like the other aggregators.

### Fixed (behaviour)

- A source that throws a non-`ProxyScraperException` (e.g. `\TypeError`,
  `\RuntimeException` from a custom/buggy scraper) no longer aborts the whole batch.
  Any foreign throwable is wrapped in a `ScraperException` (with the original kept as
  `$previous`) and captured in `errors()`, honouring the documented "a failing source
  never aborts the batch" guarantee.
- A source whose URL already carries a query string (e.g. `pubproxy.com`'s
  `?limit=5&format=json`) no longer produces a malformed, double-`?` URL when
  `scraperConfig` options are supplied — the options are now appended with `&`.
- `HeadersValidation` no longer raises an "undefined array key" warning (and is
  correctly reported invalid) when a 200 response's JSON omits the echoed `method`
  key.
- `AnonymityLevelValidation` now uses strict comparisons when scanning the echo
  response for the real IP and proxy-revealing header names.
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
- Re-running a `MethodsValidation` or `DomainsValidation` (calling `run()` again, e.g.
  after narrowing the configured set via `setRequestMethods()` / `setValidators()`) now
  replaces the previous results instead of merging stale ones in.
- `IpVersionValidation::$ipv4` / `$ipv6` are now nullable (default `null`), like the
  other aggregators' result properties; reading them before `run()` returns `null`
  instead of throwing an uninitialised-property error.
- `HeadersValidation` no longer fails with a confusing wrapped `TypeError` when probed
  with an HTTP method outside its known set — an unknown method simply sends the common
  header set.

### Internal

- The four custom-`get()` sources (geonode, spys.me, blogspot, checkerproxy.net) now
  fetch through the shared `ProxyScraper::fetch()` / new `fetchUrl()` helper instead of
  re-implementing the GET-and-wrap-errors boilerplate; their requests now also honour
  `scraperConfig` query parameters like the other sources.
- The serviss.it validation endpoints (whoami / ip / ipv4 / ipv6) are centralised in a
  new `Validations\ValidationEndpoints` instead of being duplicated across the
  validations.
- The two validation aggregators (`MethodsValidation`, `DomainsValidation`) share their
  read accessors through a new `KeyedResultMap` trait.
- `AnonymityLevelValidation` extracts its proxy-header detection and empty-response
  description into private methods (dropping an inline `call_user_func` closure) and
  raises a domain `ValidatorException` internally instead of a generic `\Exception`.
- `RandomUserAgent` is now `final` and its user-agent list a class constant.
- `Proxy` string parsing is extracted from the constructor into a dedicated parser.
- `ProxyScraper` base class is now `abstract` and exposes a shared `fetch()` helper;
  the four scrapers no longer duplicate the GET-and-wrap-errors boilerplate.
- The JSON scrapers' field-name configuration (`$hostProperty` etc.) moved to a
  `JsonFieldMapping` trait, and each scraper base now documents its config contract.
- Added [PHP-CS-Fixer](https://cs.symfony.com/) (`composer cs` / `composer cs:fix`)
  with a gating CI job, and swept redundant docblocks across the codebase.
