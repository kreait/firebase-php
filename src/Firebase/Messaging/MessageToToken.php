<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;

class MessageToToken implements Message
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

    public static function fromArray(array $data): self
    {
        if (!array_key_exists('token', $data)) {
            throw new InvalidArgumentException('Missing field "token"');
        }

        try {
            $message = new self($data['token']);

            if ($data['data'] ?? null) {
                $message = $message->withData($data['data']);
            }

            if ($data['notification']) {
                $message = $message->withNotification(Notification::fromArray($data['notification']));
            }
        } catch (\Throwable $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }

        return $message;
    }

    public function jsonSerialize()
    {
        return [
            'message' => array_filter([
                'token' => $this->token,
                'data' => $this->data,
                'notification' => $this->notification,
            ]),
        ];
    }
}
