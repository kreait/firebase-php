<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Messaging;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Tests\IntegrationTestCase;

/**
 * @internal
 */
final class AppInstanceTest extends IntegrationTestCase
{
    public Messaging $messaging;

    protected function setUp(): void
    {
        $this->messaging = self::$factory->createMessaging();
    }

    public function testItIsSubscribedToTopics(): void
    {
        $token = $this->getTestRegistrationToken();

        $firstTopic = \bin2hex(\random_bytes(5)).__FUNCTION__;
        $secondTopic = \bin2hex(\random_bytes(5)).__FUNCTION__;

        $this->messaging->subscribeToTopic($firstTopic, $token);
        $this->messaging->subscribeToTopic($secondTopic, $token);

        $instance = $this->messaging->getAppInstance($token);

        $this->assertTrue($instance->isSubscribedToTopic($firstTopic));
        $this->assertTrue($instance->isSubscribedToTopic($secondTopic));

        $this->messaging->unsubscribeFromTopic($firstTopic, $token);
        $this->messaging->unsubscribeFromTopic($secondTopic, $token);

        $instance = $this->messaging->getAppInstance($token);

        $this->assertFalse($instance->isSubscribedToTopic($firstTopic));
        $this->assertFalse($instance->isSubscribedToTopic($secondTopic));
    }
}
