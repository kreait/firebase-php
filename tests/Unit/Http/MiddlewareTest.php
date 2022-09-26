<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Http;

use Closure;
use GuzzleHttp\Psr7\Request;
use Kreait\Firebase\Http\Middleware;
use Kreait\Firebase\Tests\UnitTestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 */
final class MiddlewareTest extends UnitTestCase
{
    private Request $request;
    private Closure $handler;

    protected function setUp(): void
    {
        $this->request = new Request('GET', 'http://domain.tld');
        $this->handler = static fn (RequestInterface $request) => $request;
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
}
