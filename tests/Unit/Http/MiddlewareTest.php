<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Http;

use GuzzleHttp\Psr7;
use Kreait\Firebase\Http\Middleware;
use Kreait\Firebase\Tests\UnitTestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 */
class MiddlewareTest extends UnitTestCase
{
    private Psr7\Request $request;

    private \Closure $handler;

    protected function setUp(): void
    {
        $this->request = new Psr7\Request('GET', 'http://domain.tld');
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
