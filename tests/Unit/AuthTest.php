<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit;

use DateTimeImmutable;
use Firebase\Auth\Token\Domain\Generator;
use Firebase\Auth\Token\Domain\Verifier;
use Firebase\Auth\Token\Exception\IssuedInTheFuture;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Kreait\Clock\FrozenClock;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Auth\ApiClient;
use Kreait\Firebase\Tests\UnitTestCase;
use Kreait\Firebase\Util\JSON;
use Kreait\Firebase\Value\Provider;
use Lcobucci\JWT\Token;

/**
 * @internal
 */
final class AuthTest extends UnitTestCase
{
    /** @var FrozenClock */
    private $clock;

    /** @var MockHandler */
    private $mockHandler;

    /** @var ApiClient */
    private $apiClient;

    private $tokenGenerator;
    private $idTokenVerifier;

    /** @var Auth */
    private $auth;

    protected function setUp()
    {
        $this->mockHandler = new MockHandler();
        $this->clock = new FrozenClock(new DateTimeImmutable());

        $this->tokenGenerator = $this->createMock(Generator::class);
        $this->idTokenVerifier = $this->createMock(Verifier::class);
        $this->apiClient = new ApiClient(new Client(['handler' => $this->mockHandler]));
        $this->auth = new Auth($this->apiClient, $this->tokenGenerator, $this->idTokenVerifier);
    }

    public function testGetApiClient()
    {
        $this->assertSame($this->apiClient, $this->auth->getApiClient());
    }

    public function testCreateCustomToken()
    {
        $this->tokenGenerator
            ->expects($this->once())
            ->method('createCustomToken');

        $this->auth->createCustomToken('uid');
    }

    public function testVerifyIdToken()
    {
        $this->idTokenVerifier
            ->expects($this->once())
            ->method('verifyIdToken');

        $this->auth->verifyIdToken('some id token string');
    }

    public function testDisallowFutureTokens()
    {
        $tokenProphecy = $this->prophesize(Token::class);
        $tokenProphecy->getClaim('iat')->willReturn(\date('U'));

        $token = $tokenProphecy->reveal();

        $this->idTokenVerifier
            ->expects($this->once())
            ->method('verifyIdToken')
            ->willThrowException(new IssuedInTheFuture($token));

        $this->expectException(IssuedInTheFuture::class);
        $this->auth->verifyIdToken('foo');
    }

    public function testAllowFutureTokens()
    {
        $tokenProphecy = $this->prophesize(Token::class);
        $tokenProphecy->getClaim('iat')->willReturn(\date('U'));

        $token = $tokenProphecy->reveal();

        $this->idTokenVerifier
            ->expects($this->once())
            ->method('verifyIdToken')
            ->willReturn($token);

        $verifiedToken = $this->auth->verifyIdToken('foo', false);
        $this->assertSame($token, $verifiedToken);
    }

    public function testLinkGoogleAccountThroughIdToken()
    {
        $federatedData = [
            'federatedId' => 'https://accounts.google.com/123456789012345678901',
            'providerId' => 'google.com',
            'email' => 'user@gmail.com',
            'emailVerified' => true,
            'firstName' => 'First',
            'fullName' => 'First Last',
            'photoUrl' => 'https://lh3.googleusercontent.com/a-/AAuE7mD3yDp6gsOr7xlNYPjP6kVVhjjQ771wdgNu29Sh=s96-c',
            'originalEmail' => 'user@domain.tld',
            'localId' => 'firebase-uid',
            'displayName' => 'Display Name',
            'idToken' => 'id-token',
            'refreshToken' => 'refresh-token',
            'expiresIn' => 3600,
            'oauthAccessToken' => 'oauth-access-token',
            'oauthIdToken' => 'oauth-id-token',
            'rawUserInfo' => '{}',
            'kind' => 'identitytoolkit#VerifyAssertionResponse',
            'createdAt' => $this->clock->now()->getTimestamp(),
        ];

        $userData = [
            'users' => [[
                'localId' => 'firebase-uid',
                'idToken' => 'idToken',
                'createdAt' => $this->clock->now()->getTimestamp(),
            ]],
        ];

        $this->mockHandler->append(new Response(200, ['Content-Type' => 'application/json'], JSON::encode($federatedData)));
        $this->mockHandler->append(new Response(200, ['Content-Type' => 'application/json'], JSON::encode($userData)));

        $result = $this->auth->linkProviderThroughIdToken(Provider::GOOGLE, 'some-id-token');

        $this->assertSame('id-token', $result->idToken);
        $this->assertSame('oauth-access-token', $result->oauthAccessToken);
        $this->assertSame('refresh-token', $result->refreshToken);
    }

    public function testLinkGoogleAccountThroughAccessToken()
    {
        $federatedData = [
            'federatedId' => 'https://accounts.google.com/123456789012345678901',
            'providerId' => 'google.com',
            'email' => 'user@gmail.com',
            'emailVerified' => true,
            'firstName' => 'First',
            'fullName' => 'First Last',
            'photoUrl' => 'https://lh3.googleusercontent.com/a-/AAuE7mD3yDp6gsOr7xlNYPjP6kVVhjjQ771wdgNu29Sh=s96-c',
            'originalEmail' => 'user@domain.tld',
            'localId' => 'firebase-uid',
            'displayName' => 'Display Name',
            'idToken' => 'id-token',
            'refreshToken' => 'refresh-token',
            'expiresIn' => 3600,
            'oauthAccessToken' => 'oauth-access-token',
            'oauthIdToken' => 'oauth-id-token',
            'rawUserInfo' => '{}',
            'kind' => 'identitytoolkit#VerifyAssertionResponse',
            'createdAt' => $this->clock->now()->getTimestamp(),
        ];

        $userData = [
            'users' => [[
                'localId' => 'firebase-uid',
                'idToken' => 'idToken',
                'createdAt' => $this->clock->now()->getTimestamp(),
            ]],
        ];

        $this->mockHandler->append(new Response(200, ['Content-Type' => 'application/json'], JSON::encode($federatedData)));
        $this->mockHandler->append(new Response(200, ['Content-Type' => 'application/json'], JSON::encode($userData)));

        $result = $this->auth->linkProviderThroughAccessToken(Provider::GOOGLE, 'some-access-token');

        $this->assertSame('id-token', $result->idToken);
        $this->assertSame('oauth-access-token', $result->oauthAccessToken);
        $this->assertSame('refresh-token', $result->refreshToken);
    }
}
