<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;

/**
 * @deprecated 4.14 Use CloudMessage instead
 */
class MessageToRegistrationToken implements Message
{
    use MessageTrait;

    /**
     * @var RegistrationToken
     */
    private $token;

    private function __construct(RegistrationToken $token)
    {
        $this->token = $token;
    }

    /**
     * @deprecated 4.14 Use CloudMessage::withTarget('token', $token) instead
     * @see CloudMessage::withTarget()
     *
     * @param RegistrationToken|string $token
     *
     * @return MessageToRegistrationToken
     */
    public static function create($token): self
    {
        $token = $token instanceof RegistrationToken ? $token : RegistrationToken::fromValue($token);

        return new self($token);
    }

    /**
     * @deprecated 4.14 Use CloudMessage::fromArray() instead
     * @see CloudMessage::fromArray()
     *
     * @param array $data
     *
     * @throws InvalidArgumentException
     *
     * @return MessageToRegistrationToken
     */
    public static function fromArray(array $data): self
    {
        if (!\array_key_exists('token', $data)) {
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
        // TODO Change this to return a RegistrationToken instance in 5.0
        return (string) $this->token;
    }

    public function jsonSerialize()
    {
        return \array_filter([
            'token' => $this->token,
            'data' => $this->data,
            'notification' => $this->notification,
            'android' => $this->androidConfig,
            'apns' => $this->apnsConfig,
            'webpush' => $this->webPushConfig,
        ]);
    }
}
