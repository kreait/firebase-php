<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Exception;

use Exception;
use GuzzleHttp\Exception\RequestException;
use Kreait\Firebase\Exception\HasRequestAndResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @internal
 */
final class HasRequestAndResponseTraitTest extends TestCase
{
    /** @test */
    public function it_has_nothing()
    {
        $sut = new class() extends Exception {
            use HasRequestAndResponse;
        };

        $this->assertNull($sut->request());
        $this->assertNull($sut->getRequest());
        $this->assertNull($sut->response());
        $this->assertNull($sut->getResponse());
    }

    /** @test */
    public function it_has_a_previous_which_is_not_a_request_exception()
    {
        $previous = new Exception();

        $sut = new class('Foo', 0, $previous) extends Exception {
            use HasRequestAndResponse;
        };

        $this->assertNull($sut->request());
        $this->assertNull($sut->getRequest());
        $this->assertNull($sut->response());
        $this->assertNull($sut->getResponse());
    }

    /** @test */
    public function it_has_a_previous_request_exception()
    {
        $previous = new RequestException(
            'Foo',
            $request = $this->createMock(RequestInterface::class),
            $response = $this->createMock(ResponseInterface::class)
        );

        $sut = new class('Foo', 0, $previous) extends Exception {
            use HasRequestAndResponse;
        };

        $this->assertSame($request, $sut->request());
        $this->assertSame($request, $sut->getRequest());
        $this->assertSame($response, $sut->response());
        $this->assertSame($response, $sut->getResponse());
    }

    /** @test */
    public function it_has_a_pre_previous_request_exception()
    {
        $prePrevious = new RequestException(
            'Foo',
            $request = $this->createMock(RequestInterface::class),
            $response = $this->createMock(ResponseInterface::class)
        );

        $previous = new Exception('Foo', 0, $prePrevious);

        $sut = new class('Foo', 0, $previous) extends Exception {
            use HasRequestAndResponse;
        };

        $this->assertSame($request, $sut->request());
        $this->assertSame($request, $sut->getRequest());
        $this->assertSame($response, $sut->response());
        $this->assertSame($response, $sut->getResponse());
    }
}
