# Proxy Scraper and Validator

This library is designed to scrape free proxy resources and also individually validate those capabilities.
Support for http/https/socks4/socks5 proxies.

***WARNING!*** Keep in mind that free public proxies is HIGHLY not recommended for sensitive data transfer.

Please check out [all examples](https://github.com/IlmLV/proxy-scraper/tree/master/examples).

## Installation
Recomended installation method is via composer:
```
composer require ilmlv/proxy-scraper
```

## Proxy scraper sources
Currently implemented proxy sources:
- [blogspotproxy.blogspot.com](https://blogspotproxy.blogspot.com/)
- [checkerproxy.net](https://checkerproxy.net)
- [clarketm/proxy-list](https://github.com/clarketm/proxy-list/blob/master/proxy-list.txt)
- [free-proxy-list.net](https://www.free-proxy-list.net)
- [free-proxy-list.net/anonymous-proxy.html](https://free-proxy-list.net/anonymous-proxy.html)
- [free-proxy-list.net/uk-proxy.html](https://free-proxy-list.net/uk-proxy.html)
- [gimmeproxy.com](https://gimmeproxy.com)
- [multiproxy.org](https://multiproxy.org)
- [proxyserverlist24.top](http://www.proxyserverlist24.top)
- [pubproxy.com](http://pubproxy.com/)
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

Feel free to request more sources.

### Proxy scrapers
Keep in mind that there is prepared multiple types of scraping libraries that can be used to simplify creation of your own source scrapers.
Currently supported source data types:
- [JSON list scraper](https://github.com/IlmLV/proxy-scraper/tree/master/src/Scrapers/JsonListScrapper.php)
- [JSON object scraper](https://github.com/IlmLV/proxy-scraper/tree/master/src/Scrapers/JsonScrapper.php)
- [Table list scraper](https://github.com/IlmLV/proxy-scraper/tree/master/src/Scrapers/TableListScraper.php)
- [Plain Text list scraper](https://github.com/IlmLV/proxy-scraper/tree/master/src/Scrapers/TextListScrapper.php)

## Proxy validator
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

### Validator example
```php
$validator = new IlmLV\ProxyScraper\Validators\ProxyValidator('http://1.1.1.1:80');
dump($validator->validate());
```
Result:
```json
{
  "valid": true,
  "anonymityLevel": "elite",
  "ip": {
    "valid": true,
    "countryIsoCode": "NL",
    "organisation": "NForce Entertainment B.V."
  },
  "https": {
    "valid": true,
    "latency": 0.54314708709717,
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
  "methods": {
    "get": {
      "valid": true,
      "latency": 0.19053816795349,
      "headers": [...]
    },
    "post": {
      "valid": false,
      "latency": null,
      "error": {
        "message": "Connection to proxy closed for \"http://whoami.serviss.it/?format=json\".",
        "file": "/proxy-scraper/vendor/symfony/http-client/Chunk/ErrorChunk.php",
        "line": "56"
      },
      "headers": []
    },
    "put": {
      "valid": true,
      "latency": 2.1179740428925,
      "headers": [...]
    },
    "options": {
      "valid": true,
      "latency": 1.0257298946381,
      "headers": [...]
    },
    "head": {
      "valid": true,
      "latency": 1.9323780536652,
      "headers": [...]
    },
    "delete": {
      "valid": true,
      "latency": 0.52144622802734,
      "headers": [...]
    },
    "patch": {
      "valid": true,
      "latency": 0.42012906074524,
      "headers": [...]
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
  "latency": {
    "http": 1.0346992413203,
    "https": 0.54314708709717
  },
  "validatedAt": {
    "date": "2022-12-12 23:09:03.938495",
    "timezone_type": 3,
    "timezone": "Europe/Riga"
  }
}

```

## TODO:
- Add capability to add custom domain validators
- Reduce dependencies
- Test and improve support for wider range of PHP versions
- Improve documentation
- Tighten argument strict conditions
- Add more proxy sources
- Create functional tests
- Monitor test coverage
- Expand php compatibility
