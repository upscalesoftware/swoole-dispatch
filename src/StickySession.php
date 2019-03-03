<?php
namespace Upscale\Swoole\Dispatch;

/**
 * Dispatch requests to workers according to session ID for sticky session aka session affinity.
 * All requests belonging to a session will be dispatched to a dedicated worker process.
 * Session ID is recognized in a query string and cookies in that order of priority.
 *
 * This strategy is complimentary to the session locking and can compensate for the lack of thereof.
 * It prevents race conditions in workers competing for an exclusive lock of the same session ID.
 * Workers only pick up requests of their respective sessions as well as anonymous requests.
 */
class StickySession
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
     * @param string $sessionName
     * @param string $sessionIdLength
     */
    public function __construct($sessionName, $sessionIdLength)
    {
        $this->sidMarker = $sessionName . '=';
        $this->sidMarkerLength = strlen($this->sidMarker);
        $this->sidLength = $sessionIdLength;
    }

    /**
     * Resolve requests to corresponding workers based on session ID
     *
     * @param \Swoole\Http\Server $server
     * @param int $fd Client ID number
     * @param int $type Dispatch type
     * @param string $data Request packet data (0-8180 bytes)
     * @return int Worker ID number
     */
    public function __invoke(\Swoole\Http\Server $server, $fd, $type, $data)
    {
        if (array_key_exists($fd, $this->dispatchMap)) {
            $workerId = $this->dispatchMap[$fd];
        } else {
            $sessionId = $this->extractSessionId($data);
            $requestId = $sessionId ? crc32($sessionId) : ($fd - 1);
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
