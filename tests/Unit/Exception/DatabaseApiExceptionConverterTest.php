<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Exception;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Kreait\Firebase\Exception\Database\ApiConnectionFailed;
use Kreait\Firebase\Exception\Database\DatabaseError;
use Kreait\Firebase\Exception\Database\PermissionDenied;
use Kreait\Firebase\Exception\DatabaseApiExceptionConverter;
use Kreait\Firebase\Exception\DatabaseException;
use Kreait\Firebase\Tests\UnitTestCase;
use Kreait\Firebase\Util\JSON;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 */
final class DatabaseApiExceptionConverterTest extends UnitTestCase
{
    /** @var DatabaseApiExceptionConverter */
    private $converter;

    protected function setUp(): void
    {
        $this->converter = new DatabaseApiExceptionConverter();
    }

    /** @test */
    public function it_converts_a_request_exception_that_does_not_include_valid_json()
    {
        $requestExcpeption = new RequestException(
            'Error without valid json',
            new Request('GET', 'https://domain.tld'),
            new Response(400, [], $responseBody = '{"what is this"')
        );

        $convertedError = $this->converter->convertException($requestExcpeption);

        $this->assertInstanceOf(DatabaseError::class, $convertedError);
        $this->assertSame($responseBody, $convertedError->getMessage());
    }

    /** @test */
    public function it_converts_a_connect_exception()
    {
        $connectException = new ConnectException(
            'curl error xx',
            $this->createMock(RequestInterface::class)
        );

        $this->assertInstanceOf(ApiConnectionFailed::class, $this->converter->convertException($connectException));
    }

    /**
     * @test
     *
     * @see https://github.com/kreait/firebase-php/issues/295
     */
    public function it_handles_an_extended_error_format_in_a_response()
    {
        // see https://firebase.google.com/docs/reference/rest/auth/#section-error-response
        $e = new ClientException(
            'Unused',
            $this->createMock(RequestInterface::class),
            new Response(400, [], '{"error": {"message": "Foo"}}')
        );

        $result = $this->converter->convertException($e);

        $this->assertInstanceOf(DatabaseException::class, $result);
        $this->assertStringContainsString('Foo', $result->getMessage());
        $this->assertSame($e, $result->getPrevious());
    }

    /** @test */
    public function it_converts_a_401_response_to_a_permission_denied_error()
    {
        $e = new ClientException(
            'Foo',
            $this->createMock(RequestInterface::class),
            new Response(401, [], JSON::encode(['error' => 'Permission denied']))
        );

        $result = $this->converter->convertException($e);

        $this->assertInstanceOf(PermissionDenied::class, $result);
    }

    /** @test */
    public function it_converts_a_403_response_to_a_permission_denied_error()
    {
        $e = new ClientException(
            'Foo',
            $this->createMock(RequestInterface::class),
            new Response(403, [], JSON::encode(['error' => 'Permission denied']))
        );

        $result = $this->converter->convertException($e);

        $this->assertInstanceOf(PermissionDenied::class, $result);
    }

    /** @test */
    public function it_uses_the_response_body_as_message_when_no_json_is_present()
    {
        $e = new ClientException(
            'Foo',
            $this->createMock(RequestInterface::class),
            new Response(500, [], $body = '<html><body>Some server exception</body></html>')
        );
        $result = $this->converter->convertException($e);

        $this->assertSame($body, $result->getMessage());
    }
}
