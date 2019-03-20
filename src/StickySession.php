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
     * @param string|null $sessionName
     * @param string|null $sessionIdLength
     */
    public function __construct(DispatchInterface $guestDispatch, $sessionName = null, $sessionIdLength = null)
    {
        parent::__construct(
            $guestDispatch,
            $sessionName ?: session_name(),
            '[0-9a-zA-Z,-]{' . ($sessionIdLength ?: '22,256') . '}'
        );
    }
}
