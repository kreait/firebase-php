<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit;

use DateTimeImmutable;
use Firebase\Auth\Token\Domain\Generator;
use Firebase\Auth\Token\Domain\Verifier;
use Firebase\Auth\Token\Exception\InvalidToken;
use Firebase\Auth\Token\Exception\IssuedInTheFuture;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use Kreait\Clock\FrozenClock;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Auth\ApiClient;
use Kreait\Firebase\Exception\Auth\ExpiredOobCode;
use Kreait\Firebase\Exception\Auth\InvalidOobCode;
use Kreait\Firebase\Tests\Unit\Util\AuthError;
use Kreait\Firebase\Tests\UnitTestCase;
use Kreait\Firebase\Util\JSON;
use Kreait\Firebase\Value\Provider;
use Lcobucci\JWT\Token;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use RuntimeException;

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

    public function testFailIfUserHasBeenDeletedInTheMeantime()
    {
        $uid = 'uid';

        $tokenProphecy = $this->prophesize(Token::class);
        $tokenProphecy->getClaim('sub', Argument::cetera())->willReturn($uid);
        $tokenProphecy->getClaim('auth_time')->willReturn(\date('U'));

        $token = $tokenProphecy->reveal();

        // getAccountInfo response
        $this->mockHandler->append(new Response(200, ['Content-Type' => 'application/json'], '{}'));

        $this->idTokenVerifier->method('verifyIdToken')->with($token)->willReturn($token);

        $this->expectException(InvalidToken::class);
        $this->expectExceptionMessageRegExp('/found/i');
        $this->auth->verifyIdToken($token, true);
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

    /**
     * @dataProvider validActionCodeSettings
     */
    public function testGetActionCodeLinkWithSettings($settings)
    {
        $this->mockHandler->append(new Response(200, ['Content-Type' => 'application/json'], JSON::encode(['oobLink' => 'https://domain.tld'])));

        $this->auth->getEmailActionLink('PASSWORD_RESET', 'user@domain.tld', $settings);

        $this->addToAssertionCount(1);
    }

    /**
     * @dataProvider validActionCodeSettings
     */
    public function testSendActionCodeLinkWithSettings($settings)
    {
        $this->mockHandler->append(new Response(200, ['Content-Type' => 'application/json']));

        $this->auth->sendEmailActionLink('PASSWORD_RESET', 'user@domain.tld', $settings);

        $this->addToAssertionCount(1);
    }

    public function validActionCodeSettings()
    {
        return [
            'empty' => [[]],
            'array' => [['continueUrl' => 'https://domain.tld']],
            'object' => [new Auth\ActionCodeSettings\RawActionCodeSettings([])],
        ];
    }

    public function testVerifyPasswordResetCode()
    {
        $this->mockHandler->append($this->passwordResetSuccess('user@domain.tld'));

        $this->auth->verifyPasswordResetCode('any');
        $this->addToAssertionCount(1);
    }

    public function testVerifyInvalidPasswordResetCode()
    {
        $this->mockHandler->append($this->clientException(JSON::encode(new AuthError('invalid_oob_code'))));

        $this->expectException(InvalidOobCode::class);
        $this->auth->verifyPasswordResetCode('any');
    }

    public function testVerifyExpiredPasswordResetCode()
    {
        $this->mockHandler->append($this->clientException(JSON::encode(new AuthError('expired_oob_code'))));

        $this->expectException(ExpiredOobCode::class);
        $this->auth->verifyPasswordResetCode('any');
    }

    public function testConfirmPasswordResetWithInvalidPassword()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->auth->confirmPasswordReset('any', 'short'); // A password must be at least 6 chars
    }

    public function testConfirmPasswordResetWithInvalidResetCode()
    {
        $this->mockHandler->append($this->clientException(JSON::encode(new AuthError('invalid_oob_code'))));

        $this->expectException(InvalidOobCode::class);
        $this->auth->confirmPasswordReset('any', 'new password');
    }

    public function testConfirmPasswordResetWithExpiredResetCode()
    {
        $this->mockHandler->append($this->clientException(JSON::encode(new AuthError('expired_oob_code'))));

        $this->expectException(ExpiredOobCode::class);
        $this->auth->confirmPasswordReset('any', 'new password');
    }

    public function testConfirmPasswordResetWithoutSessionInvalidation()
    {
        $this->mockHandler->append($this->passwordResetSuccess('email@domain.tld'));
        $this->mockHandler->append(new RuntimeException('This should not have been handled'));

        $this->auth->confirmPasswordReset('valid', 'new password', false);
        $this->addToAssertionCount(1);
    }

    public function testConfirmPasswordResetWithSessionInvalidationButWithoutEmailInTheResponse()
    {
        $this->mockHandler->append($this->passwordResetSuccess());
        $this->mockHandler->append(new RuntimeException('This should not have been handled'));

        $this->auth->confirmPasswordReset('valid', 'new password', true);
        $this->addToAssertionCount(1);
    }

    private function clientException(string $body, int $code = 400): ClientException
    {
        $response = new Response($code, [], $body);

        return new ClientException('Client Exception', $this->createMock(RequestInterface::class), $response);
    }

    private function passwordResetSuccess(string $email = null)
    {
        return new Response(200, [], JSON::encode(\array_filter([
            'email' => $email,
            'requestType' => 'PASSWORD_RESET',
        ])));
    }
}
