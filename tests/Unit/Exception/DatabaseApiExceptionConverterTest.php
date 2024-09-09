<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Exception;

use Beste\Json;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Kreait\Firebase\Exception\Database\ApiConnectionFailed;
use Kreait\Firebase\Exception\Database\DatabaseError;
use Kreait\Firebase\Exception\Database\DatabaseNotFound;
use Kreait\Firebase\Exception\Database\PermissionDenied;
use Kreait\Firebase\Exception\DatabaseApiExceptionConverter;
use Kreait\Firebase\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
final class DatabaseApiExceptionConverterTest extends UnitTestCase
{
    private DatabaseApiExceptionConverter $converter;
    private Request $request;

    protected function setUp(): void
    {
        $this->converter = new DatabaseApiExceptionConverter();
        $this->request = new Request('GET', 'https://my-project.firebaseio.com');
    }

    #[Test]
    public function itConvertsARequestExceptionThatDoesNotIncludeValidJson(): void
    {
        $requestException = new RequestException(
            'Error without valid json',
            $this->request,
            new Response(400, [], $responseBody = '{"what is this"'),
        );

        $convertedError = $this->converter->convertException($requestException);

        $this->assertInstanceOf(DatabaseError::class, $convertedError);
        $this->assertSame($responseBody, $convertedError->getMessage());
    }

    #[Test]
    public function itConvertsAConnectException(): void
    {
        $connectException = new ConnectException(
            'curl error xx',
            $this->request,
        );

        $this->assertInstanceOf(ApiConnectionFailed::class, $this->converter->convertException($connectException));
    }

    /**
     * @see https://github.com/kreait/firebase-php/issues/295
     */
    #[Test]
    public function itHandlesAnExtendedErrorFormatInAResponse(): void
    {
        // see https://firebase.google.com/docs/reference/rest/auth/#section-error-response
        $e = new ClientException(
            'Unused',
            $this->request,
            new Response(400, [], '{"error": {"message": "Foo"}}'),
        );

        $result = $this->converter->convertException($e);

        $this->assertStringContainsString('Foo', $result->getMessage());
        $this->assertSame($e, $result->getPrevious());
    }

    #[Test]
    public function itConvertsA401ResponseToAPermissionDeniedError(): void
    {
        $e = new ClientException(
            'Foo',
            $this->request,
            new Response(401, [], Json::encode(['error' => 'Permission denied'])),
        );

        $result = $this->converter->convertException($e);

        $this->assertInstanceOf(PermissionDenied::class, $result);
    }

    #[Test]
    public function itConvertsA403ResponseToAPermissionDeniedError(): void
    {
        $e = new ClientException(
            'Foo',
            $this->request,
            new Response(403, [], Json::encode(['error' => 'Permission denied'])),
        );

        $result = $this->converter->convertException($e);

        $this->assertInstanceOf(PermissionDenied::class, $result);
    }

    #[Test]
    public function itConvertsA404ResponseToADatabaseNotFoundError(): void
    {
        $e = new ClientException(
            'Foo',
            $this->request,
            new Response(404, [], Json::encode(['error' => 'Not found'])),
        );

        $result = $this->converter->convertException($e);

        $this->assertInstanceOf(DatabaseNotFound::class, $result);
    }

    #[Test]
    public function itUsesTheResponseBodyAsMessageWhenNoJsonIsPresent(): void
    {
        $e = new ClientException(
            'Foo',
            $this->request,
            new Response(500, [], $body = '<html><body>Some server exception</body></html>'),
        );
        $result = $this->converter->convertException($e);

        $this->assertSame($body, $result->getMessage());
    }
}
