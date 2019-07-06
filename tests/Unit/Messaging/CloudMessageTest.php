<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\MessageTarget;
use Kreait\Firebase\Messaging\Notification;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class CloudMessageTest extends TestCase
{
    public function testInvalidTargetCausesError()
    {
        $this->expectException(InvalidArgumentException::class);
        CloudMessage::withTarget('invalid_target', 'foo');
    }

    public function testWithChangedTarget()
    {
        $original = CloudMessage::withTarget(MessageTarget::TOKEN, 'bar')
            ->withData(['foo' => 'bar'])
            ->withNotification(Notification::create('title', 'body'));

        $changed = $original->withChangedTarget(MessageTarget::TOKEN, 'baz');

        $encodedOriginal = \json_decode(\json_encode($original), true);
        $encodedOriginal[MessageTarget::TOKEN] = 'baz';

        $encodedChanged = \json_decode(\json_encode($changed), true);

        $this->assertSame($encodedOriginal, $encodedChanged);
    }

    public function testAnEmptyMessageHasNotTarget()
    {
        $this->assertFalse(CloudMessage::new()->hasTarget());
    }

    /**
     * @dataProvider multipleTargets
     */
    public function testAMessageCanOnlyHaveOneTarget($data)
    {
        $this->expectException(InvalidArgument::class);
        CloudMessage::fromArray($data);
    }

    public function multipleTargets()
    {
        return [
            'condition and token' => [[
                MessageTarget::CONDITION => 'something',
                MessageTarget::TOKEN => 'something else',
            ]],
            'condition and topic' => [[
                MessageTarget::CONDITION => 'something',
                MessageTarget::TOPIC => 'something else',
            ]],
            'token and topic' => [[
                MessageTarget::TOKEN => 'something',
                MessageTarget::TOPIC => 'something else',
            ]],
            'all of them' => [[
                MessageTarget::CONDITION => 'something',
                MessageTarget::TOKEN => 'something else',
                MessageTarget::TOPIC => 'something even elser',
            ]],
        ];
    }
}
