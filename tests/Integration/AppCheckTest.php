<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\AppCheck\AppCheckToken;
use Kreait\Firebase\AppCheck\VerifyAppCheckTokenResponse;
use Kreait\Firebase\Contract\AppCheck;
use Kreait\Firebase\Tests\IntegrationTestCase;

/**
 * @internal
 *
 * @group database-emulator
 * @group emulator
 */
final class AppCheckTest extends IntegrationTestCase
{
    public AppCheck $appCheck;
    public string $appId;

    protected function setUp(): void
    {
        $this->appCheck = self::$factory->createAppCheck();
        $this->appId = 'com.example.test-app';
    }

    public function testCreateToken(): void
    {
        $token = $this->appCheck->createToken($this->appId);

        $this->assertIsString($token->token());
        $this->assertIsNumeric($token->ttl());
        $this->assertEquals(3600, $token->ttl());
    }

    public function testCreateTokenWithCustomTtl(): void
    {
        $token = $this->appCheck->createToken($this->appId, [
            'ttl' => 1800,
        ]);

        $this->assertIsString($token->token());
        $this->assertIsNumeric($token->ttl());
        $this->assertEquals(1800, $token->ttl());
    }

    public function testVerifyToken(): void
    {
        $token = $this->appCheck->createToken($this->appId);

        $response = $this->appCheck->verifyToken($token->token());

        $this->assertIsString($response->token()->app_id());
        $this->assertEquals($this->appId, $response->token()->app_id());
    }
}
