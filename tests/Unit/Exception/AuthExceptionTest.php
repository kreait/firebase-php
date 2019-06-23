<?php

namespace Kreait\Firebase\Tests\Unit\Exception;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use Kreait\Firebase\Exception\Auth\InvalidCustomToken;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Tests\UnitTestCase;
use Psr\Http\Message\RequestInterface;

class AuthExceptionTest extends UnitTestCase
{
    /**
     * @var RequestInterface
     */
    private $request;

    protected function setUp()
    {
        $this->request = $this->createMock(RequestInterface::class);
    }

    public function testWithoutResponse()
    {
        $re = new RequestException('foo', $this->createMock(RequestInterface::class));
        $e = AuthException::fromRequestException($re);

        $this->assertInstanceOf(AuthException::class, $e);
        $this->assertSame('foo', $e->getMessage());
    }

    public function testWithInvalidJson()
    {
        $re = new RequestException(
            'foo',
            $this->createMock(RequestInterface::class),
            new Response(400, [], '{')
        );

        $this->assertSame('foo', AuthException::fromRequestException($re)->getMessage());
    }

    public function testUnknownError()
    {
        $error = $this->createFirebaseError('foo');

        $e = AuthException::fromRequestException($error);

        $this->assertInstanceOf(AuthException::class, $e);
    }

    public function testErrors()
    {
        foreach (AuthException::$errors as $identifier => $exceptionClass) {
            $error = $this->createFirebaseError(\sprintf('foo %s bar', $identifier));

            $e = AuthException::fromRequestException($error);

            $this->assertInstanceOf($exceptionClass, $e);
        }
    }

    public function testMissingPassword()
    {
        $error = $this->createFirebaseError('foo INVALID_CUSTOM_TOKEN bar');

        $e = AuthException::fromRequestException($error);

        $this->assertInstanceOf(InvalidCustomToken::class, $e);
    }

    private function createFirebaseError(string $message): RequestException
    {
        return new RequestException(
            'Firebase Error Test',
            $this->createMock(RequestInterface::class),
            new Response(400, [], \json_encode([
                'error' => [
                    'errors' => [
                        'domain' => 'global',
                        'reason' => 'invalid',
                        'message' => $message,
                    ],
                    'code' => 400,
                    'message' => $message,
                ],
            ]))
        );
    }
}
