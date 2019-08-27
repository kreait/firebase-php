<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\Tests\IntegrationTestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 */
class FactoryTest extends IntegrationTestCase
{
    public function testItUsesCustomHttpClientOptions()
    {
        $invocations = 0;

        $firebase = self::$factory->withHttpClientConfig([
            'on_headers' => static function () use (&$invocations) {
                ++$invocations;
            },
        ])->create();

        // Any request will do
        $firebase->getDatabase()->getRuleSet();
        $this->assertGreaterThan(0, $invocations);
    }

    public function testItUsesAdditionalMiddlewares()
    {
        $invocations = 0;

        $firebase = self::$factory->withHttpClientMiddlewares([
            static function (callable $handler) use (&$invocations) {
                return static function (RequestInterface $request, array $options) use ($handler, &$invocations) {
                    ++$invocations;

                    return $handler($request, $options);
                };
            },
        ])->create();

        // Any request will do
        $firebase->getDatabase()->getRuleSet();
        $this->assertTrue($invocations > 0);
    }
}
