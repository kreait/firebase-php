<?php

namespace Kreait\Tests\Firebase\Http;

use GuzzleHttp\Psr7;
use Kreait\Firebase\Http\Auth;
use Kreait\Firebase\Http\Middleware;
use Kreait\Tests\FirebaseTestCase;
use Psr\Http\Message\RequestInterface;

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
}
