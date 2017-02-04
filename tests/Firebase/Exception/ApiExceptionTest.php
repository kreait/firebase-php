<?php

namespace Tests\Firebase\Exception;

use Firebase\Exception\ApiException;
use Firebase\Exception\PermissionDenied;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Tests\FirebaseTestCase;

class ApiExceptionTest extends FirebaseTestCase
{
    public function testWrapApiException()
    {
        $source = new ApiException('Foo');
        $result = ApiException::wrapThrowable($source);

        $this->assertSame($source, $result);
    }

    public function testWrapClientException()
    {
        $source = new ClientException('Foo', $this->createMock(RequestInterface::class));
        $result = ApiException::wrapThrowable($source);

        $this->assertInstanceOf(ApiException::class, $result);
        $this->assertSame($source, $result->getPrevious());
    }

    public function testWrapClientExceptionBeingPermissionDenied()
    {
        $response = new Response(401, [], json_encode(['error' => 'Permission denied']));
        $source = new ClientException('Foo', $this->createMock(RequestInterface::class), $response);
        $result = ApiException::wrapThrowable($source);

        $this->assertInstanceOf(PermissionDenied::class, $result);
        $this->assertSame($source, $result->getPrevious());
    }

    public function testWrapRequestException()
    {
        $source = new RequestException('Foo', $this->createMock(RequestInterface::class));
        $result = ApiException::wrapThrowable($source);

        $this->assertInstanceOf(ApiException::class, $result);
        $this->assertSame($source, $result->getPrevious());
    }

    public function testWrapAnyException()
    {
        $source = new \Exception('Foo');
        $result = ApiException::wrapThrowable($source);

        $this->assertInstanceOf(ApiException::class, $result);
        $this->assertSame($source, $result->getPrevious());
    }
}
