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
        return $this->fallback->__invoke($server, $fd, $type, $data);
    }
}
