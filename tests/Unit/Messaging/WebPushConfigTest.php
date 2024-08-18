<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Kreait\Firebase\Messaging\WebPushConfig;
use Kreait\Firebase\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 *
 * @phpstan-import-type WebPushConfigShape from WebPushConfig
 * @phpstan-import-type WebPushHeadersShape from WebPushConfig
 */
final class WebPushConfigTest extends UnitTestCase
{
    /**
     * @param array<string, mixed> $data
     */
    #[DataProvider('validDataProvider')]
    #[Test]
    public function createFromValidPayload(array $data): void
    {
        $config = WebPushConfig::fromArray($data);

        $this->assertEqualsCanonicalizing($data, $config->jsonSerialize());
    }

    #[Test]
    public function itCanHaveAPriority(): void
    {
        $config = WebPushConfig::new()->withVeryLowUrgency();
        $this->assertSame('very-low', $config->jsonSerialize()['headers']['Urgency'] ?? null);

        $config = WebPushConfig::new()->withLowUrgency();
        $this->assertSame('low', $config->jsonSerialize()['headers']['Urgency'] ?? null);

        $config = WebPushConfig::new()->withNormalUrgency();
        $this->assertSame('normal', $config->jsonSerialize()['headers']['Urgency'] ?? null);

        $config = WebPushConfig::new()->withHighUrgency();
        $this->assertSame('high', $config->jsonSerialize()['headers']['Urgency'] ?? null);
    }

    /**
     * @param WebPushHeadersShape $headers
     */
    #[DataProvider('validHeaders')]
    #[Test]
    public function itAcceptsValidHeaders(array $headers): void
    {
        WebPushConfig::fromArray(['headers' => $headers]);

        $this->addToAssertionCount(1);
    }

    /**
     * @param WebPushHeadersShape $headers
     */
    #[DataProvider('invalidHeaders')]
    #[Test]
    public function itRejectsInvalidHeaders(array $headers): void
    {
        $this->expectException(InvalidArgument::class);

        WebPushConfig::fromArray(['headers' => $headers]);
    }

    /**
     * @return iterable<array<WebPushConfigShape>>
     */
    public static function validDataProvider(): iterable
    {
        yield 'full_config' => [
            [
                // https://firebase.google.com/docs/cloud-messaging/admin/send-messages#webpush_specific_fields
                'headers' => [
                    'Urgency' => 'normal',
                ],
                'notification' => [
                    'title' => '$GOOGLE up 1.43% on the day',
                    'body' => '$GOOGLE gained 11.80 points to close at 835.67, up 1.43% on the day.',
                    'icon' => 'https://my-server.example/icon.png',
                ],
            ],
        ];
    }

    public static function validHeaders(): \Iterator
    {
        yield 'positive int ttl' => [['TTL' => 1]];
        yield 'positive string ttl' => [['TTL' => '1']];
        yield 'null (#719)' => [['TTL' => null]];
    }

    public static function invalidHeaders(): \Iterator
    {
        yield 'negative int ttl' => [['TTL' => -1]];
        yield 'negative string ttl' => [['TTL' => '-1']];
        yield 'zero int ttl' => [['TTL' => 0]];
        yield 'zero string ttl' => [['TTL' => '0']];
        yield 'unsupported urgency' => [['Urgency' => 'unsupported']];
    }
}
