<?php
/**
 * Copyright © Upscale Software. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */
namespace Upscale\Swoole\Dispatch;

/**
 * Dispatch requests to workers according to session ID for sticky session a.k.a. session affinity.
 * Session ID is recognized in a query string and cookies in that order of priority.
 * Guest requests without the session context wll be delegated to a specified fallback strategy.
 */
class StickySession extends StickyCookie
{
    /**
     * Inject dependencies
     *
     * @param DispatchInterface $guestDispatch
     * @param string $sessionName
     * @param string $sessionIdLength
     */
    public function __construct(DispatchInterface $guestDispatch, $sessionName, $sessionIdLength)
    {
        parent::__construct($guestDispatch, $sessionName, '[0-9a-zA-Z,-]{' . $sessionIdLength . '}');
    }
}
