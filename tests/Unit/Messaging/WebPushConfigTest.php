<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Kreait\Firebase\Messaging\WebPushConfig;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 * @phpstan-import-type WebPushConfigShape from WebPushConfig
 * @phpstan-import-type WebPushHeadersShape from WebPushConfig
 */
final class WebPushConfigTest extends UnitTestCase
{
    /**
     * @dataProvider validDataProvider
     *
     * @param array<string, mixed> $data
     */
    public function testCreateFromValidPayload(array $data): void
    {
        $config = WebPushConfig::fromArray($data);

        $this->assertEquals($data, $config->jsonSerialize());
    }

    public function testItCanHaveAPriority(): void
    {
        $config = WebPushConfig::new()->withVeryLowUrgency();
        $this->assertSame('very-low', $config->jsonSerialize()['headers']['Urgency']);

        $config = WebPushConfig::new()->withLowUrgency();
        $this->assertSame('low', $config->jsonSerialize()['headers']['Urgency']);

        $config = WebPushConfig::new()->withNormalUrgency();
        $this->assertSame('normal', $config->jsonSerialize()['headers']['Urgency']);

        $config = WebPushConfig::new()->withHighUrgency();
        $this->assertSame('high', $config->jsonSerialize()['headers']['Urgency']);
    }

    /**
     * @dataProvider validHeaders
     *
     * @param WebPushHeadersShape $headers
     */
    public function testItAcceptsValidHeaders(array $headers): void
    {
        WebPushConfig::fromArray(['headers' => $headers]);

        $this->addToAssertionCount(1);
    }

    /**
     * @dataProvider invalidHeaders
     *
     * @param WebPushHeadersShape $headers
     */
    public function testItRejectsInvalidHeaders(array $headers): void
    {
        $this->expectException(InvalidArgument::class);

        WebPushConfig::fromArray(['headers' => $headers]);
    }

    /**
     * @return array<string, array<WebPushConfigShape>>
     */
    public function validDataProvider(): array
    {
        return [
            'full_config' => [
                // https://firebase.google.com/docs/cloud-messaging/admin/send-messages#webpush_specific_fields
                'headers' => [
                    'Urgency' => 'normal',
                ],
                'notification' => [
                    'title' => '$GOOG up 1.43% on the day',
                    'body' => '$GOOG gained 11.80 points to close at 835.67, up 1.43% on the day.',
                    'icon' => 'https://my-server/icon.png',
                ],
            ],
        ];
    }

    /**
     * @return array<string, array<WebPushHeadersShape>>
     */
    public function validHeaders(): array
    {
        return [
            'positive int ttl' => [['TTL' => 1]],
            'positive string ttl' => [['TTL' => '1']],
            'null (#719)' => [['TTL' => null]],
        ];
    }

    /**
     * @return array<string, array<array<string, mixed>>>
     */
    public function invalidHeaders(): array
    {
        return [
            'negative int ttl' => [['TTL' => -1]],
            'negative string ttl' => [['TTL' => '-1']],
            'zero int ttl' => [['TTL' => 0]],
            'zero string ttl' => [['TTL' => '0']],
            'unsupported urgency' => [['Urgency' => 'unsupported']],
        ];
    }
}
