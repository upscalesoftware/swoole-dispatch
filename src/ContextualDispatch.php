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
    abstract protected function dispatch(\Swoole\Server $server, $fd, $type, $data);
}
