<?php

namespace Kreait\Firebase\Tests\Unit\Exception;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use Kreait\Firebase\Exception\ApiException;
use Kreait\Firebase\Exception\PermissionDenied;
use Kreait\Firebase\Tests\UnitTestCase;
use Psr\Http\Message\RequestInterface;

class ApiExceptionTest extends UnitTestCase
{
    public function testWrapClientException()
    {
        $source = new ClientException(
            'Unused',
            $request = $this->createMock(RequestInterface::class),
            $response = new Response(500, [], '{"error": "Foo"}')
        );
        $result = ApiException::wrapRequestException($source);

        $this->assertSame('Foo', $result->getMessage());
        $this->assertSame($source, $result->getPrevious());
        $this->assertSame($request, $result->getRequest());
        $this->assertSame($response, $result->getResponse());
    }

    /**
     * @see https://github.com/kreait/firebase-php/issues/295
     */
    public function testWrapClientExceptionWithExtendedResponse()
    {
        // see https://firebase.google.com/docs/reference/rest/auth/#section-error-response
        $source = new ClientException(
            'Unused',
            $request = $this->createMock(RequestInterface::class),
            $response = new Response(500, [], '{"error": {"message": "Foo"}}')
        );
        $result = ApiException::wrapRequestException($source);

        $this->assertSame('Foo', $result->getMessage());
        $this->assertSame($source, $result->getPrevious());
        $this->assertSame($request, $result->getRequest());
        $this->assertSame($response, $result->getResponse());
    }

    public function testWrapClientExceptionBeingPermissionDenied()
    {
        $response = new Response(401, [], \json_encode(['error' => 'Permission denied']));
        $source = new ClientException('Foo', $this->createMock(RequestInterface::class), $response);
        $result = ApiException::wrapRequestException($source);

        $this->assertInstanceOf(PermissionDenied::class, $result);
        $this->assertSame($source, $result->getPrevious());
    }

    public function testWrapRequestException()
    {
        $source = new RequestException('Foo', $this->createMock(RequestInterface::class));
        $result = ApiException::wrapRequestException($source);

        $this->assertInstanceOf(ApiException::class, $result);
        $this->assertSame($source, $result->getPrevious());
    }

    public function testWrapServerExceptionWithNonJsonResponse()
    {
        $response = new Response(500, [], '<html><body>Some server exception</body></html>');
        $source = new ClientException('Foo', $this->createMock(RequestInterface::class), $response);
        $result = ApiException::wrapRequestException($source);

        $this->assertSame('Foo', $result->getMessage());
    }
}
