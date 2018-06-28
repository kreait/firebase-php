<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\Tests\IntegrationTestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class FactoryTest extends IntegrationTestCase
{
    public function testItUsesCustomHttpClientOptions()
    {
        $invocations = 0;

        $firebase = self::$factory->withHttpClientConfig([
            'on_headers' => function (ResponseInterface $response) use (&$invocations) {
                ++$invocations;
            },
        ])->create();

        // Any request will do
        $firebase->getDatabase()->getRules();
        $this->assertTrue($invocations > 0);
    }

    public function testItUsesAdditionalMiddlewares()
    {
        $invocations = 0;

        $firebase = self::$factory->withHttpClientMiddlewares([
            function (callable $handler) use (&$invocations) {
                return function (RequestInterface $request, array $options) use ($handler, &$invocations) {
                    ++$invocations;

                    return $handler($request, $options);
                };
            },
        ])->create();

        // Any request will do
        $firebase->getDatabase()->getRules();
        $this->assertTrue($invocations > 0);
    }
}
