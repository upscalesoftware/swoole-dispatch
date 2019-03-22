<?php
/**
 * Copyright © Upscale Software. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */
namespace Upscale\Swoole\Dispatch;

/**
 * Dispatch requests to workers according to a parameter passed in cookies or in a query string.
 * Parameter passed in a query string has higher priority than the one in cookies.
 * Requests without the parameter context wll be delegated to a specified fallback strategy.
 */
class StickyCookie extends DelegatedDispatch
{
    /**
     * @var string
     */
    protected $cookieName;

    /**
     * @var string 
     */
    protected $valueFormat;

    /**
     * Inject dependencies
     *
     * @param DispatchInterface $fallback Fallback dispatch strategy
     * @param string $cookieName Cookie name (and query parameter name)
     * @param string $valueFormat Cookie value PCRE pattern
     */
    public function __construct(DispatchInterface $fallback, $cookieName, $valueFormat = '[^\s;&#]+')
    {
        parent::__construct($fallback);
        $this->cookieName = preg_quote($cookieName);
        $this->valueFormat = $valueFormat;
    }

    /**
     * {@inheritdoc}
     */
    protected function dispatch(\Swoole\Server $server, $fd, $type, $data)
    {
        $requestId = $this->extractCookieValue($data);
        if ($requestId !== null) {
            $workerId = $this->resolveWorkerId($server, $requestId);
        } else {
            $workerId = parent::dispatch($server, $fd, $type, $data);
        }
        return $workerId;
    }

    /**
     * Resolve given request to a worker process to serve it
     *
     * @param \Swoole\Server $server
     * @param string $requestId
     * @return int
     */
    protected function resolveWorkerId(\Swoole\Server $server, $requestId)
    {
        return abs(crc32($requestId) % $server->setting['worker_num']);
    }

    /**
     * Extract parameter value from query string or cookies in an HTTP request message
     *
     * @param string $message
     * @return string|null
     */
    protected function extractCookieValue($message)
    {
        // Exclude request body from lookup scope
        $message = strstr($message, "\r\n\r\n", true);
        // Query string has priority over cookies as request line precedes headers
        if (preg_match("/\b$this->cookieName=($this->valueFormat)\b/", $message, $matches)) {
            return $matches[1];
        }  
        return null;
    }
}
