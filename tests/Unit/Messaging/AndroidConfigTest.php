<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Beste\Json;
use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 *
 * @phpstan-import-type AndroidConfigShape from AndroidConfig
 */
final class AndroidConfigTest extends UnitTestCase
{
    public function testItIsEmptyWhenItIsEmpty(): void
    {
        $this->assertSame('[]', Json::encode(AndroidConfig::new()));
    }

    public function testItHasADefaultSound(): void
    {
        $expected = [
            'notification' => [
                'sound' => 'default',
            ],
        ];

        $this->assertJsonStringEqualsJsonString(
            Json::encode($expected),
            Json::encode(AndroidConfig::new()->withDefaultSound())
        );
    }

    public function testItCanHaveAPriority(): void
    {
        $config = AndroidConfig::new()->withNormalMessagePriority();
        $this->assertSame('normal', $config->jsonSerialize()['priority']);

        $config = AndroidConfig::new()->withHighMessagePriority();
        $this->assertSame('high', $config->jsonSerialize()['priority']);
    }

    /**
     * @dataProvider validDataProvider
     *
     * @param AndroidConfigShape $data
     */
    public function testItCanBeCreatedFromAnArray(array $data): void
    {
        $config = AndroidConfig::fromArray($data);

        $this->assertEqualsCanonicalizing($data, $config->jsonSerialize());
    }

    /**
     * @dataProvider validTtlValues
     *
     * @param int|string $ttl
     */
    public function testItAcceptsValidTtls($ttl): void
    {
        AndroidConfig::fromArray([
            'ttl' => $ttl,
        ]);

        $this->addToAssertionCount(1);
    }

    /**
     * @dataProvider invalidTtlValues
     *
     * @param mixed $ttl
     */
    public function testItRejectsInvalidTtls($ttl): void
    {
        $this->expectException(InvalidArgument::class);

        AndroidConfig::fromArray([
            'ttl' => $ttl,
        ]);
    }

    /**
     * @return array<array-key, list<AndroidConfigShape>>
     */
    public function validDataProvider(): array
    {
        return [
            'full_config' => [[
                // https://firebase.google.com/docs/cloud-messaging/admin/send-messages#android_specific_fields
                'ttl' => '3600s',
                'priority' => 'normal',
                'notification' => [
                    'title' => '$GOOG up 1.43% on the day',
                    'body' => '$GOOG gained 11.80 points to close at 835.67, up 1.43% on the day.',
                    'icon' => 'stock_ticker_update',
                    'color' => '#f45342',
                    'sound' => 'default',
                ],
            ]],
        ];
    }

    /**
     * @return array<string, list<int|string|null>>
     */
    public function validTtlValues(): array
    {
        return [
            'positive int' => [1],
            'positive numeric string' => ['1'],
            'expected string' => ['1s'],
            'zero' => [0],
            'zero string' => ['0'],
            'zero string with suffix' => ['0s'],
            'null (#719)' => [null],
        ];
    }


    /**
     * @return array<string, list<mixed>>
     */
    public function invalidTtlValues(): array
    {
        return [
            'float' => [1.2],
            'wrong suffix' => ['1m'],
            'not numeric' => [true],
            'negative int' => [-1],
            'negative string' => ['-1'],
            'negative string with suffix' => ['-1s'],
        ];
    }
}
