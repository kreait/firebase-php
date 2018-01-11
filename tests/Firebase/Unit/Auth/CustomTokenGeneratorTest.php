<?php

namespace Kreait\Tests\Firebase\Unit\Auth;

use Kreait\Firebase\Auth\CustomTokenGenerator;
use Kreait\Tests\Firebase\Unit\UnitTestCase;

class CustomTokenGeneratorTest extends UnitTestCase
{
    /**
     * @var CustomTokenGenerator
     */
    private $generator;

    protected function setUp()
    {
        $this->generator = new CustomTokenGenerator($this->createServiceAccountMock());
    }

    public function testCreateWithUidOnly()
    {
        $token = $this->generator->create('uid');

        $this->assertSame('uid', $token->getClaim('uid'));
    }

    public function testCreateWithClaims()
    {
        $claims = ['first' => 'value', 'second' => 1, 'third' => true];

        $token = $this->generator->create('uid', $claims);

        $this->assertSame($claims, $token->getClaim('claims'));
    }

    public function testWithCustomExpiration()
    {
        $expiresAt = (new \DateTimeImmutable())->modify('+1337 minutes');
        $token = $this->generator->create('uid', [], $expiresAt);

        $this->assertSame($expiresAt->getTimestamp(), $token->getClaim('exp'));
    }

    public function testGenerateMultipleTokens()
    {
        $this->generator->create('first');
        $this->generator->create('second');

        $this->assertTrue($noExceptionWasThrown = true);
    }
}
