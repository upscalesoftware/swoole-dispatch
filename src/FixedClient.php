<?php
namespace Upscale\Swoole\Dispatch;

/**
 * Dispatch requests to workers according to client connection ID.
 * This dispatch strategy is equivalent to the built-in dispatch_mode=2.
 * 
 * @link https://www.swoole.co.uk/docs/modules/swoole-server/configuration#dispatch_mode
 */
class FixedClient implements DispatchInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(\Swoole\Server $server, $fd, $type, $data)
    {
        return ($fd - 1) % $server->setting['worker_num'];
    }
}
