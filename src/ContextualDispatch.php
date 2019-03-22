<?php
/**
 * Copyright © Upscale Software. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */
namespace Upscale\Swoole\Dispatch;

/**
 * Dispatch requests to workers according to the HTTP request message
 */
abstract class ContextualDispatch implements DispatchInterface
{
    /**
     * @var array
     */
    private $dispatchMap = [];

    /**
     * {@inheritdoc}
     */
    public function __invoke(\Swoole\Server $server, $fd, $type, $data)
    {
        if (isset($this->dispatchMap[$fd])) {
            $workerId = $this->dispatchMap[$fd];
        } else {
            $workerId = $this->dispatch($server, $fd, $type, $data);
            $this->dispatchMap[$fd] = $workerId;
        }
        if ($type == self::CONNECTION_CLOSE) {
            unset($this->dispatchMap[$fd]);
        }
        return $workerId;
    }

    /**
     * Resolve request to corresponding worker process
     *
     * @param \Swoole\Server $server
     * @param int $fd Client ID number
     * @param int $type Dispatch type
     * @param string $data Request packet data (0-8180 bytes)
     * @return int Worker ID number
     */
    protected function dispatch(\Swoole\Server $server, $fd, $type, $data)
    {
        return $this->resolveWorkerId($server, $this->extractRequestId($data));
    }

    /**
     * Resolve given request identifier to a worker process
     *
     * @param \Swoole\Server $server
     * @param string $requestId Request identifier
     * @return int Worker ID number
     */
    protected function resolveWorkerId(\Swoole\Server $server, $requestId)
    {
        return abs(crc32($requestId) % $server->setting['worker_num']);
    }

    /**
     * Extract request identifying information from a request message
     *
     * @param string $data
     * @return string
     */
    abstract protected function extractRequestId($data);
}
