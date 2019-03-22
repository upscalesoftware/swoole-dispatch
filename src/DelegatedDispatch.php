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
    public function __invoke(\Swoole\Server $server, $fd, $type, $data)
    {
        try {
            return parent::__invoke($server, $fd, $type, $data);
        } catch (\UnexpectedValueException $e) {
            return $this->fallback->__invoke($server, $fd, $type, $data); 
        }
    }

    /**
     * {@inheritdoc}
     * @throws \UnexpectedValueException
     */
    protected function dispatch(\Swoole\Server $server, $fd, $type, $data)
    {
        return $this->resolveWorkerId($server, $this->extractRequestId($data));
    }

    /**
     * Extract request identifying information from a request message
     * 
     * @param string $data
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function extractRequestId($data)
    {
        throw new \UnexpectedValueException('Request has not been identified for dispatch purposes.');
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
}
