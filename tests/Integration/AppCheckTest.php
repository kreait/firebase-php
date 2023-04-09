<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\Contract\AppCheck;
use Kreait\Firebase\Tests\IntegrationTestCase;

/**
 * @internal
 */
final class AppCheckTest extends IntegrationTestCase
{
    public AppCheck $appCheck;

    protected function setUp(): void
    {
        parent::setUp();

        if (self::$appId === null) {
            $this->markTestSkipped('AppCheck tests require an App ID');
        }

        $this->appCheck = self::$factory->createAppCheck();
    }

    public function testCreateTokenWithDefaultTtl(): void
    {
        $token = $this->appCheck->createToken(self::$appId);

        $this->assertSame('3600s', $token->ttl);
    }

    public function testCreateTokenWithCustomTtl(): void
    {
        $token = $this->appCheck->createToken(self::$appId, ['ttl' => 1800]);

        $this->assertSame('1800s', $token->ttl);
    }

    public function testVerifyToken(): void
    {
        $token = $this->appCheck->createToken(self::$appId);

        $response = $this->appCheck->verifyToken($token->token);

        $this->assertSame(self::$appId, $response->appId);
        $this->assertSame(self::$appId, $response->token->app_id);
    }
}
