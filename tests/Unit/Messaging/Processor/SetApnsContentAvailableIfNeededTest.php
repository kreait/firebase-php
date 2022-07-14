<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging\Processor;

use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Processor\SetApnsContentAvailableIfNeeded;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class SetApnsContentAvailableIfNeededTest extends TestCase
{
    private SetApnsContentAvailableIfNeeded $processor;

    protected function setUp(): void
    {
        $this->processor = new SetApnsContentAvailableIfNeeded();
    }

    public function testItDoesNotApplyIfItHasANotification(): void
    {
        $input = CloudMessage::new()->withNotification(['title' => 'Title']);

        $result = ($this->processor)($input);
        assert($result instanceof CloudMessage);

        $this->assertMissingContentAvailable($result);
    }

    public function testItDoesNotApplyIfItHasAnAlert(): void
    {
        $input = CloudMessage::new()
            ->withApnsConfig(
                ApnsConfig::new()->withSound('default') // sound, badge or alert
            );

        $result = ($this->processor)($input);
        assert($result instanceof CloudMessage);

        $this->assertMissingContentAvailable($result);
    }

    public function testItDoesNotApplyWhenItHasNoMessageData(): void
    {
        $input = CloudMessage::new();

        $result = ($this->processor)($input);
        assert($result instanceof CloudMessage);

        $this->assertMissingContentAvailable($result);
    }

    public function testItDoesNotApplyWhenItHasNoDataAtAll(): void
    {
        // No message data and no ApnsData
        $input = CloudMessage::new();

        $result = ($this->processor)($input);
        assert($result instanceof CloudMessage);

        $this->assertMissingContentAvailable($result);
    }

    public function testItAppliesWithMessageData(): void
    {
        $input = CloudMessage::new()->withData(['foo' => 'bar']);

        $result = ($this->processor)($input);
        assert($result instanceof CloudMessage);

        $this->assertContentAvailable($result);
    }

    public function testItAppliesWithApnsData(): void
    {
        $input = CloudMessage::new()
            ->withApnsConfig(
                ApnsConfig::new()->withDataField('foo', 'bar')
            );

        $result = ($this->processor)($input);
        assert($result instanceof CloudMessage);

        $this->assertContentAvailable($result);
    }

    private function assertContentAvailable(CloudMessage $message): void
    {
        $this->assertApsField($message, 'content-available', 1);
    }

    private function assertMissingContentAvailable(CloudMessage $message): void
    {
        $this->assertMissingApsField($message, 'content-available');
    }

    /**
     * @param mixed $expected
     */
    private function assertApsField(CloudMessage $message, string $name, $expected): void
    {
        $config = invade($message)->apnsConfig;
        assert($config instanceof ApnsConfig);

        $payload = invade($config)->payload;
        assert(is_array($payload));

        $aps = $payload['aps'] ?? [];

        $this->assertArrayHasKey($name, $aps);
        $this->assertSame($expected, $aps[$name]);
    }

    private function assertMissingApsField(CloudMessage $message, string $name): void
    {
        $config = invade($message)->apnsConfig;
        assert($config instanceof ApnsConfig);

        $payload = invade($config)->payload;
        assert(is_array($payload));

        $aps = $payload['aps'] ?? [];

        $this->assertArrayNotHasKey($name, $aps);
    }
}
