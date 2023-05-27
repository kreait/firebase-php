<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Http;

use InvalidArgumentException;
use Kreait\Firebase\Http\HttpClientOptions;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class HttpClientOptionsTest extends TestCase
{
    public function testOptionsCanBeSet(): void
    {
        $options = HttpClientOptions::default()
            ->withConnectTimeout(1.1)
            ->withReadTimeout(2.2)
            ->withTimeout(3.3)
            ->withProxy('https://proxy.tld')
        ;

        $this->assertSame(1.1, $options->connectTimeout());
        $this->assertSame(2.2, $options->readTimeout());
        $this->assertSame(3.3, $options->timeout());
        $this->assertSame('https://proxy.tld', $options->proxy());
    }

    public function testConnectTimeoutMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        HttpClientOptions::default()->withConnectTimeout(-0.1);
    }

    public function testReadTimeoutMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        HttpClientOptions::default()->withReadTimeout(-0.1);
    }

    public function testTimeoutMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        HttpClientOptions::default()->withTimeout(-0.1);
    }

    public function testItAcceptsSingleGuzzleClientConfigOptions(): void
    {
        $options = HttpClientOptions::default()->withGuzzleConfigOption('foo', 'bar');

        $this->assertEqualsCanonicalizing(['foo' => 'bar'], $options->guzzleConfig());
    }

    public function testItAcceptsMultipleGuzzleClientConfigOptions(): void
    {
        $options = HttpClientOptions::default()->withGuzzleConfigOptions([
            'first' => 'first value',
            'second' => 'second value',
        ]);

        $this->assertEqualsCanonicalizing(
            [
                'first' => 'first value',
                'second' => 'second value',
            ],
            $options->guzzleConfig(),
        );
    }

    public function testItRetainsPreviouslySetGuzzleConfigOptions(): void
    {
        $options = HttpClientOptions::default()
            ->withGuzzleConfigOption('existing', 'existing')
            ->withGuzzleConfigOptions(['new' => 'new'])
        ;

        $this->assertEqualsCanonicalizing(
            [
                'existing' => 'existing',
                'new' => 'new',
            ],
            $options->guzzleConfig(),
        );
    }

    public function testItAcceptsSingleCallableMiddlewares(): void
    {
        $options = HttpClientOptions::default()->withGuzzleMiddleware(static fn () => 'Foo', 'name');

        $middlewares = $options->guzzleMiddlewares();

        $this->assertCount(1, $middlewares);
        $this->assertIsCallable($middlewares[0]['middleware']);
        $this->assertSame('name', $middlewares[0]['name']);
    }

    public function testItAcceptsMultipleMiddlewares(): void
    {
        $options = HttpClientOptions::default()
            ->withGuzzleMiddlewares([
                static fn () => 'Foo',
                ['middleware' => static fn () => 'Foo', 'name' => 'Foo'],
            ])
        ;

        $middlewares = $options->guzzleMiddlewares();

        $this->assertCount(2, $middlewares);

        $this->assertIsCallable($middlewares[0]['middleware']);
        $this->assertSame('', $middlewares[0]['name']);

        $this->assertIsCallable($middlewares[1]['middleware']);
        $this->assertSame('Foo', $middlewares[1]['name']);
    }
}
