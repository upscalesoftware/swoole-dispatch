Request Dispatch for Swoole
===========================

This is a collection of request [dispatch strategies](https://www.swoole.co.uk/docs/modules/swoole-server/configuration#dispatch_func) for [Swoole](https://www.swoole.co.uk/) web-server that compliment the built-in [dispatch modes](https://www.swoole.co.uk/docs/modules/swoole-server/configuration#dispatch_mode).
Sticky session dispatch strategy also known as the session affinity is the cornerstone of this library.

**Features:**
- Sticky session
    - Dispatch requests to workers by Session ID
    - Session ID in query string
    - Session ID in cookies

## Installation

The library is to be installed via [Composer](https://getcomposer.org/) as a dependency:
```bash
composer require upscale/swoole-dispatch
```
## Dispatch Strategies

### Sticky Session

Sticky session also known as session affinity dispatches request to worker processes by Session ID.
It recognizes the Session ID passed in the query string and cookie headers in that order of priority.

Dispatch of guest requests without the session context wll be delegated to a specified fallback strategy.

Register the sticky session dispatcher:
```php
require 'vendor/autoload.php';

$dispatcher = new \Upscale\Swoole\Dispatch\StickySession(
    new \Upscale\Swoole\Dispatch\RoundRobin(),
    session_name(),
    ini_get('session.sid_length')
);

$server = new \Swoole\Http\Server('127.0.0.1', 8080);
$server->set([
    'worker_num'    => 4,
    'dispatch_func' => $dispatcher,
]);

$server->on('request', function ($request, $response) {
    $response->header('Content-Type', 'text/plain');
    $response->end('Served by worker PID ' . getmypid() . PHP_EOL);
});

$server->start();
```

Send requests with and without the session context:
- Anonymous requests distributed between all workers:
    ```bash
    curl -s 'http://127.0.0.1:8080/?[1-4]' -H 'Connection: close'
    ```
    ```
    Served by worker PID 21601
    Served by worker PID 21602
    Served by worker PID 21603
    Served by worker PID 21604
    ```
- Session ID in query string dispatched to a dedicated worker:
    ```bash
    curl -s 'http://127.0.0.1:8080/?PHPSESSID=ExampleSessionIdentifier11&[1-4]' -H 'Connection: close'
    ```
    ```
    Served by worker PID 21603
    Served by worker PID 21603
    Served by worker PID 21603
    Served by worker PID 21603
    ```
- Session ID in cookies dispatched to a dedicated worker:
    ```bash
    curl -s 'http://127.0.0.1:8080/?[1-4]' -H 'Cookie: PHPSESSID=ExampleSessionIdentifier22' -H 'Connection: close'
    ```
    ```
    Served by worker PID 21604
    Served by worker PID 21604
    Served by worker PID 21604
    Served by worker PID 21604
    ```

## Contributing

Pull Requests with fixes and improvements are welcome!

## License

Licensed under the [Apache License, Version 2.0](http://www.apache.org/licenses/LICENSE-2.0).