<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Http;

use Kreait\Firebase\Http\HttpClientOptions;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class HttpClientOptionsTest extends TestCase
{
    /**
     * @test
     */
    public function options_can_be_set(): void
    {
        $options = HttpClientOptions::default()
            ->withConnectTimeout(1.1)
            ->withReadTimeout(2.2)
            ->withTimeout(3.3)
            ->withProxy('https://proxy.tld');

        $this->assertSame(1.1, $options->connectTimeout());
        $this->assertSame(2.2, $options->readTimeout());
        $this->assertSame(3.3, $options->timeout());
        $this->assertSame('https://proxy.tld', $options->proxy());
    }

    /**
     * @test
     */
    public function connect_timeout_must_be_positive(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        HttpClientOptions::default()->withConnectTimeout(-0.1);
    }

    /**
     * @test
     */
    public function read_timeout_must_be_positive(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        HttpClientOptions::default()->withReadTimeout(-0.1);
    }

    /**
     * @test
     */
    public function timeout_must_be_positive(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        HttpClientOptions::default()->withTimeout(-0.1);
    }
}
