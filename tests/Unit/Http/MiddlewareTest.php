<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Http;

use GuzzleHttp\Psr7;
use Kreait\Firebase\Http\Auth;
use Kreait\Firebase\Http\Middleware;
use Kreait\Firebase\Tests\UnitTestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 */
class MiddlewareTest extends UnitTestCase
{
    /** @var Psr7\Request */
    private $request;

    /** @var \Closure */
    private $handler;

    protected function setUp(): void
    {
        $this->request = new Psr7\Request('GET', 'http://domain.tld');
        $this->handler = static function (RequestInterface $request, array $options = []) {
            return $request;
        };
    }

    public function testEnsureJsonSuffix(): void
    {
        $middleware = Middleware::ensureJsonSuffix();
        $handlerClosure = $middleware($this->handler);
        /** @var RequestInterface $request */
        $request = $handlerClosure($this->request);

        $this->assertInstanceOf(RequestInterface::class, $request);
        $this->assertStringEndsWith('.json', $request->getUri()->getPath());
    }

    public function testOverrideAuth(): void
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
