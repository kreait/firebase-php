<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use InvalidArgumentException;
use Iterator;
use Kreait\Firebase\Messaging\MessageData;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Stringable;

use function hex2bin;

/**
 * @internal
 */
final class MessageDataTest extends TestCase
{
    /**
     * @param array<non-empty-string, Stringable|string> $data
     */
    #[DataProvider('validData')]
    #[Test]
    public function itAcceptsValidData(array $data): void
    {
        MessageData::fromArray($data);
        $this->addToAssertionCount(1);
    }

    /**
     * @param array<non-empty-string, Stringable|string> $data
     */
    #[DataProvider('invalidData')]
    #[Test]
    public function itRejectsInvalidData(array $data): void
    {
        $this->expectException(InvalidArgumentException::class);
        MessageData::fromArray($data);
    }

    /**
     * @see https://github.com/kreait/firebase-php/issues/709
     */
    #[Test]
    public function itDoesNotLowerCaseKeys(): void
    {
        $input = $output = ['notificationType' => 'email'];

        $data = MessageData::fromArray($input);

        $this->assertSame($data->toArray(), $output);
    }

    public static function validData(): Iterator
    {
        yield 'integer' => [
            ['key' => 1],
        ];
        yield 'float' => [
            ['key' => 1.23],
        ];
        yield 'true' => [
            ['key' => true],
        ];
        yield 'false' => [
            ['key' => false],
        ];
        yield 'null' => [
            ['key' => null],
        ];
        yield 'object with __toString()' => [
            ['key' => new class {
                public function __toString(): string
                {
                    return 'value';
                }
            }],
        ];
        yield 'UTF-8 string' => [
            ['key' => 'Jérôme'],
        ];
    }

    public static function invalidData(): Iterator
    {
        // @see https://github.com/kreait/firebase-php/issues/441
        yield 'binary data' => [
            ['key' => hex2bin('81612bcffb')], // generated with \openssl_random_pseudo_bytes(5)
        ];
        yield 'reserved_key_from' => [
            ['from' => 'any'],
        ];
        // According to the docs, "notification" is reserved, but it's still accepted ¯\_(ツ)_/¯
        /*
        'reserved_key_notification' => [
            ['notification' => 'any'],
        ],
        */
        yield 'reserved_key_message_type' => [
            ['message_type' => 'any'],
        ];
        yield 'reserved_key_prefix_google' => [
            ['google_is_reserved' => 'any'],
        ];
        yield 'reserved_key_prefix_gcm' => [
            ['gcm_is_reserved' => 'any'],
        ];
    }
}
