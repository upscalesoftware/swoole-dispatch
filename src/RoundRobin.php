<?php
/**
 * Copyright © Upscale Software. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */
namespace Upscale\Swoole\Dispatch;

/**
 * Dispatch requests to workers in circular order using the round-robin algorithm.
 * This dispatch strategy is equivalent to the built-in dispatch_mode=1.
 * 
 * @link https://www.swoole.co.uk/docs/modules/swoole-server/configuration#dispatch_mode
 */
class RoundRobin implements DispatchInterface
{
    /**
     * @var int
     */
    protected $requestId = 0;

    /**
     * {@inheritdoc}
     */
    public function __invoke(\Swoole\Server $server, $fd, $type, $data)
    {
        $workerId = $this->requestId % $server->setting['worker_num'];
        if ($type != self::CONNECTION_CLOSE) {
            $this->requestId++;
        }
        return $workerId;
    }
}
