<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Exception;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Kreait\Firebase\Exception\Auth\ApiConnectionFailed;
use Kreait\Firebase\Exception\Auth\AuthError;
use Kreait\Firebase\Exception\Auth\CredentialsMismatch;
use Kreait\Firebase\Exception\Auth\EmailExists;
use Kreait\Firebase\Exception\Auth\EmailNotFound;
use Kreait\Firebase\Exception\Auth\ExpiredOobCode;
use Kreait\Firebase\Exception\Auth\InvalidCustomToken;
use Kreait\Firebase\Exception\Auth\InvalidOobCode;
use Kreait\Firebase\Exception\Auth\InvalidPassword;
use Kreait\Firebase\Exception\Auth\MissingPassword;
use Kreait\Firebase\Exception\Auth\OperationNotAllowed;
use Kreait\Firebase\Exception\Auth\PhoneNumberExists;
use Kreait\Firebase\Exception\Auth\ProviderLinkFailed;
use Kreait\Firebase\Exception\Auth\UserDisabled;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Exception\Auth\WeakPassword;
use Kreait\Firebase\Exception\AuthApiExceptionConverter;
use Kreait\Firebase\Tests\UnitTestCase;
use Kreait\Firebase\Util\JSON;
use Psr\Http\Message\RequestInterface;
use RuntimeException;

/**
 * @internal
 */
final class AuthApiExceptionConverterTest extends UnitTestCase
{
    /** @var AuthApiExceptionConverter */
    private $converter;

    protected function setUp(): void
    {
        $this->converter = new AuthApiExceptionConverter();
    }

    /**
     * @test
     */
    public function it_converts_a_request_exception_that_does_not_include_valid_json(): void
    {
        $requestExcpeption = new RequestException(
            'Error without valid json',
            new Request('GET', 'https://domain.tld'),
            new Response(400, [], $responseBody = '{"what is this"')
        );

        $convertedError = $this->converter->convertException($requestExcpeption);

        $this->assertInstanceOf(AuthError::class, $convertedError);
        $this->assertSame($responseBody, $convertedError->getMessage());
    }

    /**
     * @test
     */
    public function it_converts_a_connect_exception(): void
    {
        $connectException = new ConnectException(
            'curl error xx',
            $this->createMock(RequestInterface::class)
        );

        $this->assertInstanceOf(ApiConnectionFailed::class, $this->converter->convertException($connectException));
    }

    /**
     * @test
     */
    public function it_can_handle_unknown_exceptions(): void
    {
        $this->assertInstanceOf(AuthError::class, $this->converter->convertException(new RuntimeException()));
    }

    /**
     * @test
     * @dataProvider requestErrors
     *
     * @param class-string<object> $expectedClass
     */
    public function it_converts_request_exceptions_because(string $identifier, string $expectedClass): void
    {
        $requestException = new RequestException(
            'Firebase Error Test',
            new Request('GET', 'https://domain.tld'),
            new Response(400, [], JSON::encode([
                'error' => [
                    'errors' => [
                        'domain' => 'global',
                        'reason' => 'invalid',
                        'message' => $identifier,
                    ],
                    'code' => 400,
                    'message' => 'Some error that might include the idenfier "'.$identifier.'"',
                ],
            ])));

        $convertedError = $this->converter->convertException($requestException);

        $this->assertInstanceOf($expectedClass, $convertedError);
    }

    public function requestErrors()
    {
        return [
            'credentials mismatch' => ['CREDENTIALS_MISMATCH', CredentialsMismatch::class],
            'an email already exists' => ['EMAIL_EXISTS', EmailExists::class],
            'an email was not found' => ['EMAIL_NOT_FOUND', EmailNotFound::class],
            'a custom token is invalid' => ['INVALID_CUSTOM_TOKEN', InvalidCustomToken::class],
            'a password is invalid' => ['INVALID_PASSWORD', InvalidPassword::class],
            'a password is missing' => ['MISSING_PASSWORD', MissingPassword::class],
            'an operation is not allowed' => ['OPERATION_NOT_ALLOWED', OperationNotAllowed::class],
            'a user is disabled' => ['USER_DISABLED', UserDisabled::class],
            'a user was not found' => ['USER_NOT_FOUND', UserNotFound::class],
            'a password is too weak' => ['WEAK_PASSWORD', WeakPassword::class],
            'a phone number already exists' => ['PHONE_NUMBER_EXISTS', PhoneNumberExists::class],
            'an idp credential is invalid' => ['INVALID_IDP_RESPONSE', ProviderLinkFailed::class],
            'an idp credential is already linked' => ['FEDERATED_USER_ID_ALREADY_LINKED', ProviderLinkFailed::class],
            'an id token is invalid or expired' => ['INVALID_ID_TOKEN', ProviderLinkFailed::class],
            'an oob code is expired' => ['EXPIRED_OOB_CODE', ExpiredOobCode::class],
            'an oob code is invalid' => ['INVALID_OOB_CODE', InvalidOobCode::class],
        ];
    }
}
