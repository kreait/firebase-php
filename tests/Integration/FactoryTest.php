<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Kreait\Firebase\Tests\IntegrationTestCase;

/**
 * @internal
 */
final class FactoryTest extends IntegrationTestCase
{
    /**
     * @test
     */
    public function it_accepts_a_custom_guzzle_http_handler(): void
    {
        $inputHandler = new MockHandler([$response = new Response()]);

        $apiClient = self::$factory->createApiClient(['handler' => $inputHandler]);

        $this->assertInstanceOf(HandlerStack::class, $apiClient->getConfig('handler'));

        $this->assertSame($response, $apiClient->request('GET', 'https://domain.tld'));
    }
}
