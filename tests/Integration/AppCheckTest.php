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

        $this->appCheck = self::$factory->createAppCheck();
    }

    public function testCreateToken(): void
    {
        $token = $this->appCheck->createToken(self::$appId);

        $this->assertIsString($token->token);
        $this->assertSame('3600s', $token->ttl);
    }

    public function testCreateTokenWithCustomTtl(): void
    {
        $token = $this->appCheck->createToken(self::$appId, [
            'ttl' => 1800,
        ]);

        $this->assertIsString($token->token);
        $this->assertSame('1800s', $token->ttl);
    }

    public function testVerifyToken(): void
    {
        $token = $this->appCheck->createToken(self::$appId);

        $response = $this->appCheck->verifyToken($token->token);

        $this->assertIsString($response->appId);
        $this->assertEquals(self::$appId, $response->appId);
        $this->assertIsString($response->token->app_id);
        $this->assertEquals(self::$appId, $response->token->app_id);
    }
}
