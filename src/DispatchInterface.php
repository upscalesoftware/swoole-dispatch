<?php
/**
 * Copyright © Upscale Software. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */
namespace Upscale\Swoole\Dispatch;

interface DispatchInterface
{
    /**#@+
     * Connection dispatch type
     *
     * @link https://www.swoole.co.uk/docs/modules/swoole-server/configuration#dispatch_func
     */
    const CONNECTION_FETCH  = 10;
    const CONNECTION_START  = 5;
    const CONNECTION_CLOSE  = 4;
    /**#@-*/

    /**
     * Resolve requests to corresponding worker processes
     *
     * @param \Swoole\Server $server
     * @param int $fd Client ID number
     * @param int $type Dispatch type
     * @param string $data Request packet data (0-8180 bytes)
     * @return int Worker ID number
     */
    public function __invoke(\Swoole\Server $server, $fd, $type, $data);
}
