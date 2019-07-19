<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

/**
 * @deprecated 4.27.0 Use CloudMessage instead
 * @codeCoverageIgnore
 */
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
     * @var AndroidConfig|null
     */
    protected $androidConfig;

    /**
     * @var ApnsConfig|null
     */
    protected $apnsConfig;

    /**
     * @var WebPushConfig|null
     */
    protected $webPushConfig;

    /** @var FcmOptions|null */
    protected $fcmOptions;

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
     * @return AndroidConfig|null
     */
    public function androidConfig()
    {
        return $this->androidConfig;
    }

    /**
     * @return ApnsConfig|null
     */
    public function apnsConfig()
    {
        return $this->apnsConfig;
    }

    /**
     * @return WebPushConfig|null
     */
    public function webPushConfig()
    {
        return $this->webPushConfig;
    }

    /**
     * @return FcmOptions|null
     */
    public function fcmOptions()
    {
        return $this->fcmOptions;
    }

    /**
     * @return static
     */
    public function withData(array $data)
    {
        $message = clone $this;
        $message->data = $data;

        return $message;
    }

    /**
     * @return static
     */
    public function withAndroidConfig(AndroidConfig $androidConfig)
    {
        $message = clone $this;
        $message->androidConfig = $androidConfig;

        return $message;
    }

    /**
     * @return static
     */
    public function withApnsConfig(ApnsConfig $apnsConfig)
    {
        $message = clone $this;
        $message->apnsConfig = $apnsConfig;

        return $message;
    }

    /**
     * @return static
     */
    public function withWebPushConfig(WebPushConfig $webPushConfig)
    {
        $message = clone $this;
        $message->webPushConfig = $webPushConfig;

        return $message;
    }

    /**
     * @param FcmOptions|array $options
     */
    public function withFcmOptions($options): self
    {
        $message = clone $this;
        $message->fcmOptions = $options instanceof FcmOptions ? $options : FcmOptions::fromArray($options);

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
