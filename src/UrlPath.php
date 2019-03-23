<?php
/**
 * Copyright © Upscale Software. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */
namespace Upscale\Swoole\Dispatch;

/**
 * Dispatch requests to workers according to a URL path of matching HTTP methods
 */
class UrlPath extends DelegatedDispatch
{
    /**#@+
     * URL path PCRE patterns 
     */
    const PATH_CLEAN = '[^\s?]+';
    const PATH_QUERY = '[^\s]+';
    /**#@-*/
    
    /**
     * @var string
     */
    protected $methodPattern;

    /**
     * @var string
     */
    protected $pathPattern;

    /**
     * Inject dependencies
     *
     * @param DispatchInterface $fallback Fallback dispatch strategy
     * @param string[] $methods HTTP methods filter
     * @param string $pathFormat URL path PCRE pattern
     */
    public function __construct(
        DispatchInterface $fallback,
        array $methods = ['HEAD', 'GET'],
        $pathFormat = self::PATH_CLEAN
    ) {
        parent::__construct($fallback);
        $this->methodPattern = implode('|', array_map('preg_quote', $methods));
        $this->pathPattern = $pathFormat;
    }

    /**
     * {@inheritdoc}
     */
    protected function extractRequestId($data)
    {
        $headerLine = strstr($data, "\r\n", true);
        if (preg_match("/^(?:$this->methodPattern)\s+(?<path>$this->pathPattern)/", $headerLine, $matches)) {
            return $matches['path'];
        }  
        return parent::extractRequestId($data);
    }
}
