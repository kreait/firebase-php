<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;

final class MessageData implements \JsonSerializable
{
    /** @var array<string, string> */
    private array $data = [];

    private function __construct()
    {
    }

    /**
     * @param array<mixed, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $messageData = new self();

        foreach ($data as $key => $value) {
            if (!self::isStringable($key) || !self::isStringable($value)) {
                throw new InvalidArgumentException('Message data must be a one-dimensional array of string(able) keys and values.');
            }

            if (self::isBinary((string) $value)) {
                throw new InvalidArgumentException(
                    "The message data field '{$key}' seems to contain binary data. As this can lead to broken messages, "
                    .'please convert it to a string representation first, e.g. with bin2hex() or base64encode().'
                );
            }

            self::assertValidKey((string) $key);

            $messageData->data[(string) $key] = (string) $value;
        }

        return $messageData;
    }

    /**
     * @return array<string, string>
     */
    public function jsonSerialize(): array
    {
        return $this->data;
    }

    /**
     * @param mixed $value
     */
    private static function isStringable($value): bool
    {
        return \is_null($value) || \is_scalar($value) || (\is_object($value) && \method_exists($value, '__toString'));
    }

    private static function isBinary(string $value): bool
    {
        return \mb_detect_encoding($value) === false;
    }

    /**
     * @see https://firebase.google.com/docs/cloud-messaging/concept-options#data_messages
     */
    private static function assertValidKey(string $value): void
    {
        $value = \mb_strtolower($value);
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
    }
}
