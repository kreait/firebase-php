<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging\Processor;

use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Processor\SetApnsPushTypeIfNeeded;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class SetApnsPushTypeIfNeededTest extends TestCase
{
    private SetApnsPushTypeIfNeeded $processor;

    protected function setUp(): void
    {
        $this->processor = new SetApnsPushTypeIfNeeded();
    }

    public function testItDoesNotOverridePreexistingPushHeaders(): void
    {
        $input = CloudMessage::new()
            ->withNotification(['title' => 'Title']) // This would normally lead to the 'alert' push type
            ->withApnsConfig(
                ApnsConfig::new()->withHeader('apns-push-type', $pushType = 'location')
            );

        $result = ($this->processor)($input);
        assert($result instanceof CloudMessage);

        $this->assertHeader($result, 'apns-push-type', $pushType);
    }

    public function testAMessageWithoutAnythingOfRelevanceHasNoPushType(): void
    {
        $input = CloudMessage::new();

        $result = ($this->processor)($input);
        assert($result instanceof CloudMessage);

        $this->assertMissingHeader($result, 'apns-push-type');
    }

    public function testABackgroundMessageReceivesAnAccordingPushType(): void
    {
        $input = CloudMessage::new()->withData(['foo' => 'bar']);

        $result = ($this->processor)($input);
        assert($result instanceof CloudMessage);

        $this->assertHeader($result, 'apns-push-type', 'background');
    }

    public function testANotificationMessageReceivesAnAccordingPushType(): void
    {
        $input = CloudMessage::new()->withNotification(['title' => 'Title']);

        $result = ($this->processor)($input);
        assert($result instanceof CloudMessage);

        $this->assertHeader($result, 'apns-push-type', 'alert');
    }

    /**
     * @param mixed $expected
     */
    private function assertHeader(CloudMessage $message, string $name, $expected): void
    {
        $config = invade($message)->apnsConfig;
        assert($config instanceof ApnsConfig);

        $headers = invade($config)->headers;
        assert(is_array($headers));

        $this->assertArrayHasKey($name, $headers);
        $this->assertSame($expected, $headers[$name]);
    }

    private function assertMissingHeader(CloudMessage $message, string $name): void
    {
        $config = invade($message)->apnsConfig;
        assert($config instanceof ApnsConfig);

        $headers = invade($config)->headers;
        assert(is_array($headers));

        $this->assertArrayNotHasKey($name, $headers);
    }
}
