<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Messaging;

use Kreait\Firebase\Messaging;
use Kreait\Firebase\Tests\IntegrationTestCase;

/**
 * @internal
 */
final class AppInstanceTest extends IntegrationTestCase
{
    /** @var Messaging */
    public $messaging;

    protected function setUp(): void
    {
        $this->messaging = self::$factory->createMessaging();
    }

    /**
     * @test
     */
    public function it_is_subscribed_to_topics(): void
    {
        $token = $this->getTestRegistrationToken();

        $firstTopic = __FUNCTION__;
        $secondTopic = __FUNCTION__.'2';

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
