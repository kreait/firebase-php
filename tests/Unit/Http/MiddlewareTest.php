<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Http;

use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7;
use Kreait\Firebase\Http\Auth;
use Kreait\Firebase\Http\Middleware;
use Kreait\Firebase\Http\MultipartResponse;
use Kreait\Firebase\Tests\UnitTestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 */
class MiddlewareTest extends UnitTestCase
{
    /**
     * @var Psr7\Request
     */
    private $request;

    /**
     * @var \Closure
     */
    private $promise;

    /**
     * @var \Closure
     */
    private $handler;

    protected function setUp()
    {
        $this->request = new Psr7\Request('GET', 'http://domain.tld');
        $this->handler = static function (RequestInterface $request, array $options = []) {
            return $request;
        };
    }

    public function testEnsureJsonSuffix()
    {
        $middleware = Middleware::ensureJsonSuffix();
        $handlerClosure = $middleware($this->handler);
        /** @var RequestInterface $request */
        $request = $handlerClosure($this->request);

        $this->assertInstanceOf(RequestInterface::class, $request);
        $this->assertStringEndsWith('.json', $request->getUri()->getPath());
    }

    public function testOverrideAuth()
    {
        $authenticatedRequest = new Psr7\Request('GET', 'http://domain.tld?is_authenticated=true'); // Doesn't matter :)

        $auth = $this->createMock(Auth::class);
        $auth->expects($this->any())
            ->method('authenticateRequest')
            ->with($this->request)
            ->willReturn($authenticatedRequest);

        $middleware = Middleware::overrideAuth($auth);
        $handlerClosure = $middleware($this->handler);
        /** @var RequestInterface $request */
        $request = $handlerClosure($this->request);

        $this->assertSame($authenticatedRequest, $request);
    }

    public function testParseMultipartResponse()
    {
        $middleware = Middleware::parseMultipartResponse();
        $inner = new Promise(function () use (&$inner) { $inner->resolve(new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => ['multipart/mixed; boundary=batch_e115af19-2deb-4ed7-be7d-cc2e43699be1']])); });
        $promise = static function (RequestInterface $request, array $options = []) use (&$inner) {
            return $inner;
        };
        $handlerClosure = $middleware($promise);
        /** @var PromiseInterface $request */
        $response = $handlerClosure($this->request)->wait();

        $this->assertInstanceOf(MultipartResponse::class, $response);
    }
}
