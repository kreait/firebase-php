<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Messaging;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\RegistrationToken;
use Kreait\Firebase\Tests\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Test;

use function bin2hex;
use function random_bytes;

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

    #[Test]
    public function itIsSubscribedToTopics(): void
    {
        $token = $this->getTestRegistrationToken();

        $firstTopic = bin2hex(random_bytes(5)).__FUNCTION__;
        $secondTopic = bin2hex(random_bytes(5)).__FUNCTION__;
        $thirdTopic = bin2hex(random_bytes(5)).__FUNCTION__;

        $this->messaging->subscribeToTopic($firstTopic, $token);
        $this->messaging->subscribeToTopic($secondTopic, RegistrationToken::fromValue($token)); // Lazy registration token test
        $this->messaging->subscribeToTopic($thirdTopic, $token);

        $instance = $this->messaging->getAppInstance($token);

        $this->assertTrue($instance->isSubscribedToTopic($firstTopic));
        $this->assertTrue($instance->isSubscribedToTopic($secondTopic));

        $this->messaging->unsubscribeFromTopic($firstTopic, $token);
        $this->messaging->unsubscribeFromTopic($secondTopic, $token);
        $this->messaging->unsubscribeFromAllTopics($token);

        $instance = $this->messaging->getAppInstance($token);

        $this->assertFalse($instance->isSubscribedToTopic($firstTopic));
        $this->assertFalse($instance->isSubscribedToTopic($secondTopic));
    }
}
