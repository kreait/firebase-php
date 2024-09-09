<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use JsonSerializable;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Stringable;

use function in_array;
use function mb_detect_encoding;
use function mb_detect_order;
use function mb_strtolower;
use function str_starts_with;

final class MessageData implements JsonSerializable
{
    /**
     * @param array<non-empty-string, string> $data
     */
    private function __construct(private readonly array $data)
    {
    }

    /**
     * @param array<non-empty-string, Stringable|string> $data
     */
    public static function fromArray(array $data): self
    {
        $validated = [];

        foreach ($data as $key => $value) {
            $value = (string) $value;

            if (self::isBinary($value)) {
                throw new InvalidArgumentException(
                    "The message data field '{$key}' seems to contain binary data. As this can lead to broken messages, "
                    .'please convert it to a string representation first, e.g. with bin2hex() or base64encode().',
                );
            }

            $key = self::assertValidKey($key);

            $validated[$key] = $value;
        }

        return new self($validated);
    }

    /**
     * @return array<non-empty-string, string>
     */
    public function toArray(): array
    {
        return $this->data;
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }

    private static function isBinary(string $value): bool
    {
        return mb_detect_encoding($value, (array) mb_detect_order(), true) === false;
    }

    /**
     * @see https://firebase.google.com/docs/cloud-messaging/concept-options#data_messages
     *
     * @param non-empty-string $key
     *
     * @return non-empty-string
     */
    private static function assertValidKey(string $key): string
    {
        $check = mb_strtolower($key);

        // According to the docs, "notification" is reserved, but it's still accepted ¯\_(ツ)_/¯
        $reservedWords = ['from', /* 'notification', */ 'message_type'];
        $reservedPrefixes = ['google', 'gcm'];

        if (in_array($check, $reservedWords, true)) {
            throw new InvalidArgumentException("'{$key}' is a reserved word and can not be used as a key in FCM data payloads");
        }

        foreach ($reservedPrefixes as $prefix) {
            if (str_starts_with($check, $prefix)) {
                throw new InvalidArgumentException("'{$prefix}' is a reserved prefix and can not be used as a key in FCM data payloads");
            }
        }

        return $key;
    }
}
