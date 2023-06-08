<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use InvalidArgumentException;
use Kreait\Firebase\Messaging\MessageData;
use PHPUnit\Framework\TestCase;
use Stringable;

use function hex2bin;

/**
 * @internal
 */
final class MessageDataTest extends TestCase
{
    /**
     * @dataProvider validData
     *
     * @param array<non-empty-string, Stringable|string> $data
     *
     * @test
     */
    public function itAcceptsValidData(array $data): void
    {
        MessageData::fromArray($data);
        $this->addToAssertionCount(1);
    }

    /**
     * @dataProvider invalidData
     *
     * @param array<non-empty-string, Stringable|string> $data
     *
     * @test
     */
    public function itRejectsInvalidData(array $data): void
    {
        $this->expectException(InvalidArgumentException::class);
        MessageData::fromArray($data);
    }

    /**
     * @see https://github.com/kreait/firebase-php/issues/709
     *
     * @test
     */
    public function itDoesNotLowerCaseKeys(): void
    {
        $input = $output = ['notificationType' => 'email'];

        $data = MessageData::fromArray($input);

        $this->assertSame($data->toArray(), $output);
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public static function validData(): array
    {
        return [
            'integer' => [
                ['key' => 1],
            ],
            'float' => [
                ['key' => 1.23],
            ],
            'true' => [
                ['key' => true],
            ],
            'false' => [
                ['key' => false],
            ],
            'null' => [
                ['key' => null],
            ],
            'object with __toString()' => [
                ['key' => new class() {
                    public function __toString()
                    {
                        return 'value';
                    }
                }],
            ],
            'UTF-8 string' => [
                ['key' => 'Jérôme'],
            ],
        ];
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public static function invalidData(): array
    {
        return [
            // @see https://github.com/kreait/firebase-php/issues/441
            'binary data' => [
                ['key' => hex2bin('81612bcffb')], // generated with \openssl_random_pseudo_bytes(5)
            ],
            'reserved_key_from' => [
                ['from' => 'any'],
            ],
            // // According to the docs, "notification" is reserved, but it's still accepted ¯\_(ツ)_/¯
            /*
            'reserved_key_notification' => [
                ['notification' => 'any'],
            ],
             */
            'reserved_key_message_type' => [
                ['message_type' => 'any'],
            ],
            'reserved_key_prefix_google' => [
                ['google_is_reserved' => 'any'],
            ],
            'reserved_key_prefix_gcm' => [
                ['gcm_is_reserved' => 'any'],
            ],
        ];
    }
}
