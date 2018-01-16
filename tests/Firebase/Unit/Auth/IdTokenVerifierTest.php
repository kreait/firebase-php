<?php

namespace Kreait\Tests\Firebase\Unit\Auth;

use Firebase\Auth\Token\Domain\Verifier as BaseVerifier;
use Firebase\Auth\Token\Exception\InvalidToken as BaseVerifierException;
use Kreait\Firebase\Auth\IdTokenVerifier;
use Kreait\Firebase\Exception\Auth\InvalidIdToken;
use Kreait\Tests\Firebase\Unit\UnitTestCase;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Token;

class IdTokenVerifierTest extends UnitTestCase
{
    /**
     * @var IdTokenVerifier
     */
    private $verifier;

    /**
     * @var BaseVerifier
     */
    private $base;

    protected function setUp()
    {
        $this->base = $this->createMock(BaseVerifier::class);
        $this->verifier = new IdTokenVerifier($this->base);
    }

    public function testProblemsResultInAnException()
    {
        $token = $this->createMock(Token::class);

        $this->base
            ->expects($this->once())
            ->method('verifyIdToken')
            ->with($token)
            ->willThrowException(new BaseVerifierException($token, 'Foo'));

        $this->expectException(InvalidIdToken::class);

        $this->verifier->verify($token);
    }

    /**
     * @param $invalidValue
     * @dataProvider invalidTokens
     */
    public function testAnInvalidTokenExceptionContainsTheGivenToken($invalidValue)
    {
        $this->base
            ->expects($this->once())
            ->method('verifyIdToken')
            ->willThrowException(new \Exception('any'));

        try {
            $this->verifier->verify($invalidValue);
        } catch (InvalidIdToken $e) {
            $this->assertSame($invalidValue, $e->getToken());
        }
    }

    public function testItReturnsTheTokenOnSuccess()
    {
        $token = $this->createMock(Token::class);

        $this->base
            ->expects($this->once())
            ->method('verifyIdToken')
            ->with($token)
            ->willReturn($token);

        $this->assertSame($token, $this->verifier->verify($token));
    }

    public function testItCanHandleATokenAsString()
    {
        $token = (new Builder())->getToken();
        $tokenString = (string) $token;

        $this->base
            ->expects($this->once())
            ->method('verifyIdToken')
            ->with($tokenString)
            ->willReturn($token);

        $this->assertSame($token, $this->verifier->verify($tokenString));
    }

    public function invalidTokens()
    {
        return [
            'int' => [1],
            'bool' => [true],
            'null' => [null]
        ];
    }
}
