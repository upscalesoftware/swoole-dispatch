Request Dispatch for Swoole
===========================

This is a collection of request [dispatch strategies](https://www.swoole.co.uk/docs/modules/swoole-server/configuration#dispatch_func) for [Swoole](https://www.swoole.co.uk/) web-server that compliment the built-in [dispatch modes](https://www.swoole.co.uk/docs/modules/swoole-server/configuration#dispatch_mode).
Sticky session dispatch strategy also known as the session affinity is the cornerstone of this library.
Other strategies are provided as a fallback for guest requests without the session context.
They mimic the native dispatch modes that are by design mutually exclusive to the custom dispatch function.

**Strategies:**
- Fixed Client
    - Dispatch requests to workers by Client ID
- Round Robin
    - Dispatch requests to workers in circular order
- Sticky Session
    - Dispatch requests to workers by Session ID
    - Session ID in query string
    - Session ID in cookies
- URL Path
    - Dispatch requests to workers by URL path
    - Query string excluded or included
    - Custom URL path pattern
    - HTTP method filters

## Installation

The library is to be installed via [Composer](https://getcomposer.org/) as a dependency:
```bash
composer require upscale/swoole-dispatch
```
## Dispatch Strategies

### Fixed Client

Dispatch requests to workers according to client connection ID equivalent to the built-in [fixed dispatch mode](https://www.swoole.co.uk/docs/modules/swoole-server/configuration#dispatch_mode).

Register the dispatcher:
```php
require 'vendor/autoload.php';

$server = new \Swoole\Http\Server('127.0.0.1', 8080);
$server->set([
    'dispatch_func' => new \Upscale\Swoole\Dispatch\FixedClient(),
]);
$server->on('request', function ($request, $response) use ($server) {
    $response->header('Content-Type', 'text/plain');
    $response->end("Served by worker {$server->worker_id}\n");
});
$server->start();
```

Send some test requests:
- Different connections are distributed between all workers:
    ```bash
    curl -s 'http://127.0.0.1:8080/?[1-4]' -H 'Connection: close'

    Served by worker 0
    Served by worker 1
    Served by worker 2
    Served by worker 3
    ```
- Every connection is dispatched to a dedicated worker:
    ```bash
    curl -s 'http://127.0.0.1:8080/?[1-4]'

    Served by worker 1
    Served by worker 1
    Served by worker 1
    Served by worker 1
    ```

### Round Robin

Dispatch requests to workers in circular order equivalent to the built-in [polling dispatch mode](https://www.swoole.co.uk/docs/modules/swoole-server/configuration#dispatch_mode).

Register the dispatcher:
```php
$server->set([
    'dispatch_func' => new \Upscale\Swoole\Dispatch\RoundRobin(),
]);
```

Send some test requests:
- Requests of all connections are distributed between all workers:
    ```bash
    curl -s 'http://127.0.0.1:8080/?[1-4]' -H 'Connection: close'
    curl -s 'http://127.0.0.1:8080/?[1-4]'

    Served by worker 0
    Served by worker 1
    Served by worker 2
    Served by worker 3
    ```

### Sticky Session

Dispatch requests to workers according to session ID for sticky session also known as session affinity.
All requests belonging to a session will be dispatched to a dedicated worker process.
Session ID is recognized in a query string and cookie headers in that order of priority.

This strategy is complimentary to the session locking and can compensate for the lack of thereof.
It prevents race conditions in workers competing for an exclusive lock of the same session ID.
Workers only pick up requests of their respective sessions as well as guest requests without the session context.

Dispatch of guest requests will be delegated to a specified fallback strategy of choice.

Register the sticky session dispatcher with fallback to the Round-Robin for guests:
```php
$server->set([
    'dispatch_func' => new \Upscale\Swoole\Dispatch\StickySession(
        new \Upscale\Swoole\Dispatch\RoundRobin()
    ),
]);
```

Send some test requests with and without the session context:
- Guest requests are delegated to the fallback strategy Round-Robin:
    ```bash
    curl -s 'http://127.0.0.1:8080/?[1-4]'

    Served by worker 0
    Served by worker 1
    Served by worker 2
    Served by worker 3
    ```
- Session requests are dispatched to a dedicated worker:
    ```bash
    curl -s 'http://127.0.0.1:8080/?PHPSESSID=ExampleSessionIdentifier11&[1-4]'
    curl -s 'http://127.0.0.1:8080/?[1-4]' -H 'Cookie: PHPSESSID=ExampleSessionIdentifier11'

    Served by worker 2
    Served by worker 2
    Served by worker 2
    Served by worker 2
    ```
    
### URL Path

Dispatch requests to workers according to a URL path of a HTTP request.
Configure whether to use a query string in addition to the path or not (the default).
Custom URL path filter can be provided via the PCRE regular expression syntax.
The dispatch can also be limited to a given HTTP methods (`HEAD` and `GET` by default).

Register the URL path dispatcher with fallback to the Round-Robin:
```php
$server->set([
    'dispatch_func' => new \Upscale\Swoole\Dispatch\UrlPath(
        new \Upscale\Swoole\Dispatch\RoundRobin()
    ),
]);
```

Send some test requests:
- Requests are dispatched to a dedicated worker by URL path:
    ```bash
    curl -s 'http://127.0.0.1:8080/page1.html'
    curl -s 'http://127.0.0.1:8080/page2.html'
    curl -s 'http://127.0.0.1:8080/page3.html'
    curl -s 'http://127.0.0.1:8080/page3.html?ignored=any'

    Served by worker 2
    Served by worker 0
    Served by worker 1
    Served by worker 1
    ```

## Contributing

Pull Requests with fixes and improvements are welcome!

## License

Copyright © Upscale Software. All rights reserved.

Licensed under the [Apache License, Version 2.0](http://www.apache.org/licenses/LICENSE-2.0).