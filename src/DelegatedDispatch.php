<?php
/**
 * Copyright © Upscale Software. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */
namespace Upscale\Swoole\Dispatch;

/**
 * Delegate request dispatch to the underlying fallback dispatch strategy
 */
abstract class DelegatedDispatch extends ContextualDispatch
{
    /**
     * @var DispatchInterface
     */
    private $fallback;

    /**
     * Inject dependencies
     *
     * @param DispatchInterface $fallback Fallback dispatch strategy
     */
    public function __construct(DispatchInterface $fallback)
    {
        $this->fallback = $fallback;
    }

    /**
     * {@inheritdoc}
     */
    protected function dispatch(\Swoole\Server $server, $fd, $type, $data)
    {
        $requestId = $this->extractRequestId($data);
        if ($requestId !== null) {
            $workerId = $this->resolveWorkerId($server, $requestId);
        } else {
            $workerId = $this->fallback->__invoke($server, $fd, $type, $data);
        }
        return $workerId;
    }

    /**
     * Extract request identifying information from a request message
     * 
     * @param string $data
     * @return string|null
     */
    abstract protected function extractRequestId($data);

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
}
