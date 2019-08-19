<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Auth;

use DateTimeImmutable;
use Firebase\Auth\Token\Domain\Verifier;
use Firebase\Auth\Token\Exception\ExpiredToken;
use Firebase\Auth\Token\Exception\InvalidSignature;
use Firebase\Auth\Token\Exception\InvalidToken;
use Firebase\Auth\Token\Exception\IssuedInTheFuture;
use Firebase\Auth\Token\Exception\UnknownKey;
use InvalidArgumentException;
use Kreait\Firebase\Auth\IdTokenVerifier;
use Kreait\Firebase\Clock\FrozenClock;
use Lcobucci\JWT\Token;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use RuntimeException;
use stdClass;
use Throwable;

/**
 * @internal
 */
final class IdTokenVerifierTest extends TestCase
{
    // Mocks
    private $baseVerifier;
    private $token;

    /** @var FrozenClock */
    private $clock;

    /** @var IdTokenVerifier */
    private $verifier;

    protected function setUp()
    {
        $this->token = $this->prophesize(Token::class);
        $this->baseVerifier = $this->prophesize(Verifier::class);
        $this->clock = new FrozenClock(new DateTimeImmutable());

        $this->verifier = new IdTokenVerifier($this->baseVerifier->reveal(), $this->clock);
    }

    /**
     * @test
     * @dataProvider invalidTokens
     */
    public function it_rejects_invalid_tokens($value)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->verifier->verifyIdToken($value);
    }

    /** @test */
    public function it_works()
    {
        $revealedToken = $this->token->reveal();
        $this->baseVerifier->verifyIdToken($revealedToken)->willReturn($revealedToken);

        $this->verifier->verifyIdToken($revealedToken);
        $this->addToAssertionCount(1);
    }

    /**
     * @test
     * @dataProvider passThroughErrors
     */
    public function it_passes_through_errors(Throwable $error)
    {
        $this->baseVerifier->verifyIdToken($this->token->reveal())->willThrow($error);

        try {
            $this->verifier->verifyIdToken($this->token->reveal());
            $this->fail('An error should have been thrown');
        } catch (Throwable $e) {
            $this->assertSame($error, $e);
        }
    }

    /** @test */
    public function it_namespaces_errors()
    {
        $this->baseVerifier->verifyIdToken($this->token->reveal())->willThrow(new RuntimeException('Oops'));

        $this->expectException(InvalidToken::class);
        $this->verifier->verifyIdToken($this->token->reveal());
    }

    /** @test */
    public function it_accepts_a_non_expired_token_with_leeway()
    {
        $this->token->getClaim('exp', Argument::any())->willReturn($this->clock->now()->modify('-10 seconds')->getTimestamp());
        $revealedToken = $this->token->reveal();

        $this->baseVerifier->verifyIdToken($revealedToken)->willThrow(new ExpiredToken($revealedToken));

        try {
            $this->verifier->verifyIdToken($revealedToken);
            $this->fail('An exception should have been thrown');
        } catch (Throwable $e) {
            $this->assertInstanceOf(ExpiredToken::class, $e);
        }

        $verifier = $this->verifier->withLeewayInSeconds(10);

        $verifier->verifyIdToken($revealedToken);
        $this->addToAssertionCount(1);
    }

    /** @test */
    public function it_accepts_a_token_issued_in_the_past_with_leeway()
    {
        $this->token->getClaim('iat', Argument::any())->willReturn($this->clock->now()->modify('+10 seconds')->getTimestamp());
        $revealedToken = $this->token->reveal();

        $this->baseVerifier->verifyIdToken($revealedToken)->willThrow(new IssuedInTheFuture($revealedToken));

        try {
            $this->verifier->verifyIdToken($revealedToken);
            $this->fail('An exception should have been thrown');
        } catch (Throwable $e) {
            $this->assertInstanceOf(IssuedInTheFuture::class, $e);
        }

        $verifier = $this->verifier->withLeewayInSeconds(10);

        $verifier->verifyIdToken($revealedToken);
        $this->addToAssertionCount(1);
    }

    /** @test */
    public function it_accepts_a_token_authenticated_in_the_past_with_leeway()
    {
        $this->token->getClaim('auth_time', Argument::any())->willReturn($this->clock->now()->modify('+10 seconds')->getTimestamp());
        $revealedToken = $this->token->reveal();

        $this->baseVerifier->verifyIdToken($revealedToken)->willThrow(new InvalidToken($revealedToken, 'xxx authentication time xxx'));

        try {
            $this->verifier->verifyIdToken($revealedToken);
            $this->fail('An exception should have been thrown');
        } catch (Throwable $e) {
            $this->assertInstanceOf(InvalidToken::class, $e);
        }

        $verifier = $this->verifier->withLeewayInSeconds(10);

        $verifier->verifyIdToken($revealedToken);
        $this->addToAssertionCount(1);
    }

    public function it_requires_an_expiration_date()
    {
    }

    public function invalidTokens()
    {
        return [
            ['invalid'],
            [new stdClass()],
        ];
    }

    public function passThroughErrors()
    {
        return [
            [$this->createMock(UnknownKey::class)],
            [$this->createMock(InvalidSignature::class)],
            [$this->createMock(InvalidToken::class)],
        ];
    }
}
