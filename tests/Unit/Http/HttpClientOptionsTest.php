<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Http;

use InvalidArgumentException;
use Kreait\Firebase\Http\HttpClientOptions;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class HttpClientOptionsTest extends TestCase
{
    #[Test]
    public function optionsCanBeSet(): void
    {
        $options = HttpClientOptions::default()
            ->withConnectTimeout(1.1)
            ->withReadTimeout(2.2)
            ->withTimeout(3.3)
            ->withProxy('https://proxy.example.com')
        ;

        $this->assertEqualsWithDelta(1.1, $options->connectTimeout(), PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta(2.2, $options->readTimeout(), PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta(3.3, $options->timeout(), PHP_FLOAT_EPSILON);
        $this->assertSame('https://proxy.example.com', $options->proxy());
    }

    #[Test]
    public function connectTimeoutMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        HttpClientOptions::default()->withConnectTimeout(-0.1);
    }

    #[Test]
    public function readTimeoutMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        HttpClientOptions::default()->withReadTimeout(-0.1);
    }

    #[Test]
    public function timeoutMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        HttpClientOptions::default()->withTimeout(-0.1);
    }

    #[Test]
    public function itAcceptsSingleGuzzleClientConfigOptions(): void
    {
        $options = HttpClientOptions::default()->withGuzzleConfigOption('foo', 'bar');

        $this->assertEqualsCanonicalizing(['foo' => 'bar'], $options->guzzleConfig());
    }

    #[Test]
    public function itAcceptsMultipleGuzzleClientConfigOptions(): void
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

    #[Test]
    public function itRetainsPreviouslySetGuzzleConfigOptions(): void
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

    #[Test]
    public function itAcceptsSingleCallableMiddlewares(): void
    {
        $options = HttpClientOptions::default()->withGuzzleMiddleware(static fn() => 'Foo', 'name');

        $middlewares = $options->guzzleMiddlewares();

        $this->assertCount(1, $middlewares);
        $this->assertIsCallable($middlewares[0]['middleware']);
        $this->assertSame('name', $middlewares[0]['name']);
    }

    #[Test]
    public function itAcceptsMultipleMiddlewares(): void
    {
        $options = HttpClientOptions::default()
            ->withGuzzleMiddlewares([
                static fn() => 'Foo',
                ['middleware' => static fn() => 'Foo', 'name' => 'Foo'],
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
