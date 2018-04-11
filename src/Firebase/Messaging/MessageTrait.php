<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

trait MessageTrait
{
    /**
     * @var array|null
     */
    protected $data;

    /**
     * @var Notification|null
     */
    protected $notification;

    /**
     * @return array|null
     */
    public function data()
    {
        return $this->data;
    }

    /**
     * @return Notification|null
     */
    public function notification()
    {
        return $this->notification;
    }

    /**
     * @param array $data
     *
     * @return static
     */
    public function withData(array $data)
    {
        $message = clone $this;
        $message->data = $data;

        return $message;
    }

    /**
     * @param Notification|array $notification
     *
     * @return static
     */
    public function withNotification($notification)
    {
        $notification = $notification instanceof Notification
            ? $notification
            : Notification::fromArray((array) $notification);

        $message = clone $this;
        $message->notification = $notification;

        return $message;
    }
}
