<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;

final class MessageData implements \JsonSerializable
{
    /** @var array<non-empty-string, string> */
    private array $data = [];

    private function __construct()
    {
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $messageData = new self();

        foreach ($data as $key => $value) {
            if (!self::isStringable($key) || !self::isStringable($value)) {
                throw new InvalidArgumentException('Message data must be a one-dimensional array of string(able) keys and values.');
            }

            $key = (string) $key;
            $value = (string) $value;

            if (self::isBinary($value)) {
                throw new InvalidArgumentException(
                    "The message data field '{$key}' seems to contain binary data. As this can lead to broken messages, "
                    .'please convert it to a string representation first, e.g. with bin2hex() or base64encode().'
                );
            }

            $key = self::assertValidKey($key);

            $messageData->data[$key] = $value;
        }

        return $messageData;
    }

    /**
     * @return array<non-empty-string, string>
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * @return array<non-empty-string, string>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @param mixed $value
     */
    private static function isStringable($value): bool
    {
        return null === $value || \is_scalar($value) || (\is_object($value) && \method_exists($value, '__toString'));
    }

    private static function isBinary(string $value): bool
    {
        return \mb_detect_encoding($value, (array) \mb_detect_order(), true) === false;
    }

    /**
     * @see https://firebase.google.com/docs/cloud-messaging/concept-options#data_messages
     *
     * @return non-empty-string
     */
    private static function assertValidKey(string $value): string
    {
        $value = \mb_strtolower(trim($value));

        if ($value === '') {
            throw new InvalidArgumentException("'Empty keys are not allowed in FCM data payloads");
        }

        // According to the docs, "notification" is reserved, but it's still accepted ¯\_(ツ)_/¯
        $reservedWords = ['from', /*'notification',*/ 'message_type'];
        $reservedPrefixes = ['google', 'gcm'];

        if (\in_array($value, $reservedWords, true)) {
            throw new InvalidArgumentException("'{$value}' is a reserved word and can not be used as a key in FCM data payloads");
        }

        foreach ($reservedPrefixes as $prefix) {
            if (\str_starts_with($value, $prefix)) {
                throw new InvalidArgumentException("'{$prefix}' is a reserved prefix and can not be used as a key in FCM data payloads");
            }
        }

        return $value;
    }
}
