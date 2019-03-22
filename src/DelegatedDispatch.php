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
     * @throws \InvalidArgumentException
     */
    protected function extractRequestId($data)
    {
        throw new \UnexpectedValueException('Request has not been identified for dispatch purposes.');
    }
}
