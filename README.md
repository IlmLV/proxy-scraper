# Proxy Scraper and Validator

This library is designed to scrape free proxy resources and also individually validate those capabilities.
Support for http/https/socks4/socks5 proxies.

***WARNING!*** Keep in mind that free public proxies is HIGHLY not recommended for sensitive data transfer.

See the [usage examples](#usage) below.

## Installation
Recomended installation method is via composer:
```
composer require ilmlv/proxy-scraper
```

## Usage

The snippets below assume Composer's autoloader is already loaded
(`require __DIR__ . '/vendor/autoload.php';`). `dump()` comes from
`symfony/var-dumper` — swap it for `print_r()`/`var_dump()` if you prefer.

### Scrape all sources

```php
use IlmLV\ProxyScraper\LoadProxies;

$proxies = LoadProxies::init()->all();

foreach ($proxies->get() as $proxy) {
    echo $proxy . PHP_EOL;
}

dump($proxies->stats());
```

### Scrape a single source

```php
use IlmLV\ProxyScraper\LoadProxies;
use IlmLV\ProxyScraper\Sources\FreeProxyListNet;

$proxies = LoadProxies::init()->only(FreeProxyListNet::class);

foreach ($proxies->get() as $proxy) {
    echo $proxy . PHP_EOL;
}
```

### Scrape only sources that are due

Each source declares a cron `SCHEDULE`; `scheduled()` runs just the ones due
right now — handy for a frequently-polling cron job that should not hammer every
provider on every tick.

```php
use IlmLV\ProxyScraper\LoadProxies;

$proxies = LoadProxies::init()->scheduled();

foreach ($proxies->get() as $proxy) {
    echo $proxy . PHP_EOL;
}
```

### Inspect per-source statistics

```php
use IlmLV\ProxyScraper\LoadProxies;

$proxies = LoadProxies::init()->all();

foreach ($proxies->stats() as $source => $results) {
    echo $source . ' => ' . json_encode($results) . PHP_EOL;
}
```

### Configure a source

Extra config keys are appended to the source URL as query parameters, so you can
tune sources that accept them (e.g. pubproxy.com). You can also supply your own
Symfony HTTP client.

```php
use IlmLV\ProxyScraper\LoadProxies;
use IlmLV\ProxyScraper\Sources\PubProxyCom;
use Symfony\Component\HttpClient\HttpClient;

$scraperConfig = [
    PubProxyCom::class => [
        // 'api'  => 'xxx',
        'country' => 'US',
        'https'   => true,
        'level'   => 'elite',
    ],
];

$httpClient = HttpClient::create(['timeout' => 30]);

$proxies = LoadProxies::init($scraperConfig, $httpClient)
    ->only(PubProxyCom::class);

dump($proxies->stats());
```

### Handle scraper errors

A source that fails (network error, bad response, misconfiguration) does not
throw — the exception is captured and exposed via `errors()`, keyed by the source
class.

```php
use IlmLV\ProxyScraper\LoadProxies;
use IlmLV\ProxyScraper\Sources\PubProxyCom;

$scraperConfig = [
    PubProxyCom::class => [
        'api'            => 'wrong_key',
        'level'          => 'wrong_level',
        'wrongParameter' => 'wrong_value',
    ],
];

$proxies = LoadProxies::init($scraperConfig)->only(PubProxyCom::class);

foreach ($proxies->errors() as $scraper => $exception) {
    echo $scraper . ' => ' . $exception->getMessage() . PHP_EOL;
}
```

## Proxy scraper sources
Currently implemented proxy sources:
- [blogspotproxy.blogspot.com](https://blogspotproxy.blogspot.com/)
- [checkerproxy.net](https://checkerproxy.net)
- [clarketm/proxy-list](https://github.com/clarketm/proxy-list/blob/master/proxy-list.txt)
- [free-proxy-list.net](https://www.free-proxy-list.net)
- [free-proxy-list.net/anonymous-proxy.html](https://free-proxy-list.net/anonymous-proxy.html)
- [free-proxy-list.net/uk-proxy.html](https://free-proxy-list.net/uk-proxy.html)
- [monosans/proxy-list](https://github.com/monosans/proxy-list) (http)
- [proxyscrape.com](https://proxyscrape.com/free-proxy-list) (http/socks4/socks5)
- [pubproxy.com](http://pubproxy.com/)
- [roosterkid/openproxylist](https://github.com/roosterkid/openproxylist) (https/socks4/socks5)
- [ShiftyTR/Proxy-List](https://github.com/ShiftyTR/Proxy-List)
- [ShiftyTR/Proxy-List/https.txt](https://github.com/ShiftyTR/Proxy-List/blob/master/https.txt)
- [ShiftyTR/Proxy-List/socks4.txt](https://github.com/ShiftyTR/Proxy-List/blob/master/socks4.txt)
- [ShiftyTR/Proxy-List/socks5.txt](https://github.com/ShiftyTR/Proxy-List/blob/master/socks5.txt)
- [socks-proxy.net](https://www.socks-proxy.net)
- [sslproxies.org](https://www.sslproxies.org)
- [TheSpeedX/PROXY-List/http.txt](https://github.com/TheSpeedX/PROXY-List/blob/master/http.txt)
- [TheSpeedX/PROXY-List/socks4.txt](https://github.com/TheSpeedX/PROXY-List/blob/master/socks4.txt)
- [TheSpeedX/PROXY-List/socks5.txt](https://github.com/TheSpeedX/PROXY-List/blob/master/socks5.txt)
- [us-proxy.org](https://www.us-proxy.org)
- [vakhov/fresh-proxy-list](https://github.com/vakhov/fresh-proxy-list) (http/https/socks4/socks5)

Feel free to request more sources.

### Proxy scrapers
Keep in mind that there is prepared multiple types of scraping libraries that can be used to simplify creation of your own source scrapers.
Currently supported source data types:
- [JSON list scraper](https://github.com/IlmLV/proxy-scraper/tree/master/src/Scrapers/JsonListScrapper.php)
- [JSON object scraper](https://github.com/IlmLV/proxy-scraper/tree/master/src/Scrapers/JsonScrapper.php)
- [Table list scraper](https://github.com/IlmLV/proxy-scraper/tree/master/src/Scrapers/TableListScraper.php)
- [Plain Text list scraper](https://github.com/IlmLV/proxy-scraper/tree/master/src/Scrapers/TextListScrapper.php)

### Define a custom source

Extend one of the scraper base types, point `$url` at the resource, and hand the
class to `only()`/`add()` — there is no need to register it in `LoadProxies`:

```php
use IlmLV\ProxyScraper\LoadProxies;
use IlmLV\ProxyScraper\ScraperInterface;
use IlmLV\ProxyScraper\Scrapers\JsonScrapper;

class CustomGimmeProxy extends JsonScrapper implements ScraperInterface
{
    protected string $url = 'https://gimmeproxy.com/api/getProxy';
}

$proxies = LoadProxies::init()->only(CustomGimmeProxy::class);

foreach ($proxies->get() as $proxy) {
    echo $proxy . PHP_EOL;
}
```

## Proxy validation
This library can also be used for proxy capability validation:
- ***anonymity level***: 
  - elite (no origin IP exposure and no proxy relates headers), 
  - anonymous (has proxy related headers), 
  - exposed (has origin IP exposure)
- if proxy ***server IP*** matches server by whom request is performed
- ***HTTPS*** request support
- various ***request methods***: GET, POST, PUT, OPTIONS, HEAD, DELETE, PATCH
- huge amount of ***request headers*** if they are not modified by proxy - tested in each request method
- multiple public ***domains*** (amazon.com, craigslist.org, example.com, google.com, ss.com)
- average ***latency*** calculation

### Validate a proxy

`ProxyValidation` accepts either a proxy string or a `Proxy` entity:

```php
use IlmLV\ProxyScraper\Validations\ProxyValidation;

$validation = new ProxyValidation('http://1.1.1.1:80');

dump($validation);
```

### Scrape and validate

Validate every proxy a source returns:

```php
use IlmLV\ProxyScraper\LoadProxies;
use IlmLV\ProxyScraper\Sources\FreeProxyListNet;
use IlmLV\ProxyScraper\Validations\ProxyValidation;

$proxies = LoadProxies::init()->only(FreeProxyListNet::class);

foreach ($proxies->get() as $proxy) {
    $validation = new ProxyValidation($proxy);

    dump(json_decode(json_encode($validation)));
}
```

The validation result looks like:

```json
{
  "valid": true,
  "anonymityLevel": "elite",
  "ip": {
    "valid": true,
    "countryIsoCode": "NL",
    "organisation": "NForce Entertainment B.V."
  },
  "http": {
    "latency": 0.54314708709717,
    "get": {
      "valid": true,
      "latency": 0.19053816795349,
      "headers": {
        "A-IM": true,
        "Accept": true,
        "Accept-Charset": true,
        "Accept-Encoding": true,
        "Accept-Language": true,
        "Accept-Datetime": true,
        "Access-Control-Request-Method": true,
        "Access-Control-Request-Headers": true,
        "Authorization": true,
        "Cache-Control": true,
        "Connection": true,
        "Cookie": true,
        "Date": true,
        "Expect": true,
        "Forwarded": true,
        "From": true,
        "If-Modified-Since": true,
        "If-None-Match": true,
        "If-Range": true,
        "Max-Forwards": true,
        "Origin": true,
        "Pragma": true,
        "Range": true,
        "Referer": true,
        "TE": true,
        "User-Agent": true,
        "Upgrade": true,
        "Via": true,
        "Warning": true,
        "DNT": true,
        "X-Requested-With": true,
        "X-CSRF-Token": true,
        "X-Real-Ip": true,
        "X-Proxy-Id": true,
        "X-Forwarded": true,
        "X-Forwarded-For": true,
        "Forwarded-For": true,
        "Forwarded-For-Ip": true,
        "Client-Ip": true,
        "X-Client-Ip": true
      }
    },
    "post": {
      "valid": false,
      "latency": null,
      "error": {
        "message": "Connection to proxy closed for \"http://whoami.serviss.it/?format=json\".",
        "file": "/proxy-scraper/vendor/symfony/http-client/Chunk/ErrorChunk.php",
        "line": "56"
      },
      "headers": {}
    },
    "put": {
      "valid": true,
      "latency": 2.1179740428925,
      "headers": {...}
    },
    "options": {
      "valid": true,
      "latency": 1.0257298946381,
      "headers": {...}
    },
    "head": {
      "valid": true,
      "latency": 1.9323780536652,
      "headers": {...}
    },
    "delete": {
      "valid": true,
      "latency": 0.52144622802734,
      "headers": {...}
    },
    "patch": {
      "valid": true,
      "latency": 0.42012906074524,
      "headers": {...}
    }
  },
  "https": {
    "latency": 0.54314708709717,
    "get": {
      "valid": true,
      "latency": 0.19053816795349,
      "headers": {...}
    },
    "post": {
      "valid": false,
      "latency": null,
      "error": {
        "message": "Connection to proxy closed for \"https://whoami.serviss.it/?format=json\".",
        "file": "/proxy-scraper/vendor/symfony/http-client/Chunk/ErrorChunk.php",
        "line": "56"
      },
      "headers": []
    },
    "put": {
      "valid": true,
      "latency": 2.1179740428925,
      "headers": {...}
    },
    "options": {
      "valid": true,
      "latency": 1.0257298946381,
      "headers": {...}
    },
    "head": {
      "valid": true,
      "latency": 1.9323780536652,
      "headers": {...}
    },
    "delete": {
      "valid": true,
      "latency": 0.52144622802734,
      "headers": {...}
    },
    "patch": {
      "valid": true,
      "latency": 0.42012906074524,
      "headers": {...}
    }
  },
  "domains": {
    "amazon.com": {
      "valid": true,
      "latency": 1.7253589630127
    },
    "craigslist.org": {
      "valid": true,
      "latency": 4.507395029068
    },
    "example.com": {
      "valid": true,
      "latency": 0.4618821144104
    },
    "google.com": {
      "valid": false,
      "latency": 0.41366505622864
    },
    "ss.com": {
      "valid": true,
      "latency": 0.44051098823547
    }
  },
  "validatedAt": {
    "date": "2022-12-12 23:09:03.938495",
    "timezone_type": 3,
    "timezone": "Europe/Riga"
  }
}

```

## Testing
The library ships with a PHPUnit test suite split into two suites:

- **unit** — fully offline and deterministic. Every HTTP call is mocked with
  Symfony's `MockHttpClient`, so the entities, scraper base classes, all proxy
  sources and the validation subsystem are tested without touching the network.
- **live** — hits the real provider endpoints and asserts each source is still
  reachable and returns proxies. It doubles as a dead-provider monitor and is
  kept out of the gating pipeline.

```bash
composer install

composer test            # unit suite (offline)
composer test:coverage   # unit suite + text coverage report (needs pcov or xdebug)
composer test:live       # live suite (requires network)
```

Continuous integration runs on GitHub Actions (see `.github/workflows/ci.yml`): the
unit suite runs across PHP 8.1–8.5 with code-coverage reporting, and the live suite
runs as a separate, non-blocking job on a weekly schedule (and on demand) so dead or
drifting providers are caught early.

## TODO:
- Add capability to add custom domain validations
- Reduce dependencies
- Improve documentation
- Tighten argument strict conditions
- Add more proxy sources
- ~~Create functional tests~~ ✅
- ~~Monitor test coverage~~ ✅
- Expand PHP compatibility (CI now covers PHP 8.1–8.5)
