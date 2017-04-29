<?php

namespace Kreait\Firebase\Database;

use Kreait\Firebase\Exception\InvalidArgumentException;

/**
 * Server sent event
 */
class ServerSentEvent
{
    const END_OF_LINE = "/\r\n|\n|\r/";

    /**
     * @var string
     */
    private $data;

    /**
     * @var string
     */
    private $eventType;

    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $retry;

    /**
     * @param string   $data
     * @param string   $eventType
     * @param int|null $id
     * @param int|null $retry
     */
    public function __construct($data = '', $eventType = 'message', $id = null, $retry = null)
    {
        $this->data = $data;
        $this->eventType = $eventType;
        $this->id = $id;
        $this->retry = $retry;
    }

    /**
     * @param string $raw
     *
     * @return Event
     */
    public static function parse($raw)
    {
        $event = new static();
        $lines = preg_split(self::END_OF_LINE, $raw);

        foreach ($lines as $line) {
            $matched = preg_match('/(?P<name>[^:]*):?( ?(?P<value>.*))?/', $line, $matches);

            if (!$matched) {
                throw new InvalidArgumentException(sprintf('Invalid line %s', $line));
            }

            $name = $matches['name'];
            $value = $matches['value'];

            if ($name === '') {
                continue;
            }

            switch ($name) {
                case 'event':
                    $event->eventType = $value;
                    break;
                case 'data':
                    $event->data = empty($event->data) ? $value : "$event->data\n$value";
                    break;
                case 'id':
                    $event->id = $value;
                    break;
                case 'retry':
                    $event->retry = (int) $value;
                    break;
                default:
                    continue;
            }
        }

        return $event;
    }

    /**
     * Get Data
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get Event type
     *
     * @return string
     */
    public function getEventType()
    {
        return $this->eventType;
    }

    /**
     * Get Id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get Retry
     *
     * @return int
     */
    public function getRetry()
    {
        return $this->retry;
    }
}
