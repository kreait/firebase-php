<?php

namespace Tests\Firebase\Http;

use Firebase\Http\Auth;
use Firebase\Http\Middleware;
use GuzzleHttp\Psr7;
use Psr\Http\Message\RequestInterface;
use Tests\FirebaseTestCase;

class MiddlewareTest extends FirebaseTestCase
{
    /**
     * @var Psr7\Request
     */
    private $request;

    /**
     * @var \Closure
     */
    private $handler;

    protected function setUp()
    {
        $this->request = new Psr7\Request('GET', 'http://domain.tld');
        $this->handler = function (RequestInterface $request, array $options = []) {
            return $request;
        };
    }

    public function testEnsureJson()
    {
        $middleware = Middleware::ensureJson();
        $handlerClosure = $middleware($this->handler);
        /** @var RequestInterface $request */
        $request = $handlerClosure($this->request);

        $this->assertInstanceOf(RequestInterface::class, $request);
        $this->assertStringEndsWith('.json', $request->getUri()->getPath());
        $this->assertTrue($request->hasHeader('Content-Type'));
        $this->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
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
}
