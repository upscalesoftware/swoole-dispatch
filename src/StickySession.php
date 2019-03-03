<?php
namespace Upscale\Swoole\Dispatch;

/**
 * Dispatch requests to workers according to session ID for sticky session a.k.a. session affinity.
 * Session ID is recognized in a query string and cookies in that order of priority.
 * Guest requests without the session context wll be delegated to a specified fallback strategy.
 */
class StickySession implements DispatchInterface
{
    /**
     * @var DispatchInterface
     */
    protected $guestDispatch;
    
    /**
     * @var string
     */
    protected $sidMarker;

    /**
     * @var int
     */
    protected $sidMarkerLength;

    /**
     * @var int
     */
    protected $sidLength;

    /**
     * @var array
     */
    protected $dispatchMap = [];

    /**
     * Inject dependencies
     *
     * @param DispatchInterface $guestDispatch
     * @param string $sessionName
     * @param string $sessionIdLength
     */
    public function __construct(DispatchInterface $guestDispatch, $sessionName, $sessionIdLength)
    {
        $this->guestDispatch = $guestDispatch;
        $this->sidMarker = $sessionName . '=';
        $this->sidMarkerLength = strlen($this->sidMarker);
        $this->sidLength = $sessionIdLength;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(\Swoole\Server $server, $fd, $type, $data)
    {
        if (array_key_exists($fd, $this->dispatchMap)) {
            $workerId = $this->dispatchMap[$fd];
        } else {
            $sessionId = $this->extractSessionId($data);
            $requestId = $sessionId ? crc32($sessionId) : $this->guestDispatch->__invoke($server, $fd, $type, $data);
            $workerId = abs($requestId % $server->setting['worker_num']);
            $this->dispatchMap[$fd] = $workerId;
        }
        if ($type == self::CONNECTION_CLOSE) {
            unset($this->dispatchMap[$fd]);
        }
        return $workerId;
    }

    /**
     * Extract session ID from an HTTP request message
     *
     * @param string $message
     * @return string|null
     */
    protected function extractSessionId($message)
    {
        // Ignore request body that may contain session IDs in URLs
        $message = strstr($message, "\r\n\r\n", true);
        // Query string has priority over cookies as request line precedes headers  
        $pos = strpos($message, $this->sidMarker);
        if ($pos !== false) {
            return substr($message, $pos + $this->sidMarkerLength, $this->sidLength);
        }
        return null;
    }
}
