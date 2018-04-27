<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;

class MessageToRegistrationToken implements Message
{
    use MessageTrait;

    /**
     * @var string
     */
    private $token;

    private function __construct(string $token)
    {
        $this->token = $token;
    }

    public static function create(string $token): self
    {
        return new self($token);
    }

    /**
     * @param array $data
     *
     * @throws InvalidArgumentException
     *
     * @return MessageToRegistrationToken
     */
    public static function fromArray(array $data): self
    {
        if (!array_key_exists('token', $data)) {
            throw new InvalidArgumentException('Missing field "token"');
        }

        $message = self::create($data['token']);

        if ($data['data'] ?? null) {
            $message = $message->withData($data['data']);
        }

        if ($data['notification'] ?? null) {
            $message = $message->withNotification(Notification::fromArray($data['notification']));
        }

        if ($data['android'] ?? null) {
            $message = $message->withAndroidConfig(AndroidConfig::fromArray($data['android']));
        }

        if ($data['apns'] ?? null) {
            $message = $message->withApnsConfig(ApnsConfig::fromArray($data['apns']));
        }

        if ($data['webpush'] ?? null) {
            $message = $message->withWebPushConfig(WebPushConfig::fromArray($data['webpush']));
        }

        return $message;
    }

    public function token(): string
    {
        return $this->token;
    }

    public function jsonSerialize()
    {
        return array_filter([
            'token' => $this->token,
            'data' => $this->data,
            'notification' => $this->notification,
            'android' => $this->androidConfig,
            'apns' => $this->apnsConfig,
            'webpush' => $this->webPushConfig,
        ]);
    }
}
