<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;

/**
 * @deprecated 4.14 Use CloudMessage instead
 */
class MessageToRegistrationToken extends CloudMessage
{
    /** @var RegistrationToken */
    private $token;

    /**
     * @deprecated 4.14 Use CloudMessage::withTarget('token', $token) instead
     * @see CloudMessage::withTarget()
     *
     * @param RegistrationToken|string $token
     *
     * @return static
     */
    public static function create($token)
    {
        $token = $token instanceof RegistrationToken ? $token : RegistrationToken::fromValue($token);

        $message = static::withTarget('token', $token->value());
        $message->token = $token;

        return $message;
    }

    /**
     * @deprecated 4.14 Use CloudMessage::fromArray() instead
     * @see CloudMessage::fromArray()
     *
     * @throws InvalidArgumentException
     *
     * @return static
     */
    public static function fromArray(array $data)
    {
        if (!($token = $data['token'] ?? null)) {
            throw new InvalidArgumentException('Missing field "token"');
        }

        $token = $token instanceof RegistrationToken ? $token : RegistrationToken::fromValue((string) $token);

        $message = parent::fromArray($data);
        $message->token = $token;

        return $message;
    }

    /**
     * @deprecated 4.29.0 Use CloudMessage instead
     */
    public function token(): string
    {
        return (string) $this->token;
    }
}
