<?php
/**
 * Copyright © Upscale Software. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */
namespace Upscale\Swoole\Dispatch;

/**
 * Dispatch requests to workers according to a parameter passed in cookies or in a query string.
 * Parameter passed in a query string has higher priority than the one in cookies.
 * Requests without the parameter context wll be delegated to a specified fallback strategy.
 */
class StickyCookie extends DelegatedDispatch
{
    /**
     * @var string
     */
    protected $cookieName;

    /**
     * @var string 
     */
    protected $valueFormat;

    /**
     * Inject dependencies
     *
     * @param DispatchInterface $fallback Fallback dispatch strategy
     * @param string $cookieName Cookie name (and query parameter name)
     * @param string $valueFormat Cookie value PCRE pattern
     */
    public function __construct(DispatchInterface $fallback, $cookieName, $valueFormat = '[^\s;&#]+')
    {
        parent::__construct($fallback);
        $this->cookieName = preg_quote($cookieName);
        $this->valueFormat = $valueFormat;
    }

    /**
     * {@inheritdoc}
     */
    protected function extractRequestId($data)
    {
        // Exclude request body from lookup scope
        $data = strstr($data, "\r\n\r\n", true);
        // Query string has priority over cookies as request line precedes headers
        if (preg_match("/\b$this->cookieName=($this->valueFormat)\b/", $data, $matches)) {
            return $matches[1];
        }  
        return null;
    }
}
