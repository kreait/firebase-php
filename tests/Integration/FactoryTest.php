<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\Http\HttpClientOptions;
use Kreait\Firebase\Tests\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\RequestInterface;

use function assert;

/**
 * @internal
 */
final class FactoryTest extends IntegrationTestCase
{
    #[Test]
    public function itSupportsExtendingTheHttpClientConfig(): void
    {
        if (self::$rtdbUrl === null) {
            $this->markTestSkipped('The HTTP client Config extension test requires a database URL');
        }

        // Setting a config that the SDK definitely wouldn't do on its own
        $sink = sys_get_temp_dir().'/'.__FUNCTION__;
        $db = self::$factory
            ->withHttpClientOptions(
                HttpClientOptions::default()->withGuzzleConfigOption('sink', $sink),
            )
            ->withDatabaseUri(self::$rtdbUrl)
            ->createDatabase()
        ;

        assert(file_exists($sink) === false);

        // We're only interested in the file, not the actual result
        $db->getReference(__FUNCTION__)->shallow()->getSnapshot();

        try {
            $this->assertFileExists($sink);
        } finally {
            unlink($sink);
        }
    }

    #[Test]
    public function itSupportsAddingAdditionalHttpClientMiddlewares(): void
    {
        if (self::$rtdbUrl === null) {
            $this->markTestSkipped('The HTTP client middleware extension test requires a database URL');
        }

        $check = false;

        $middleware = static function (callable $handler) use (&$check) {
            return static function (RequestInterface $request, array $options) use ($handler, &$check) {
                $check = true;

                return $handler($request, $options);
            };
        };

        $db = self::$factory
            ->withHttpClientOptions(
                HttpClientOptions::default()->withGuzzleMiddleware($middleware),
            )
            ->withDatabaseUri(self::$rtdbUrl)
            ->createDatabase()
        ;

        // We're only interested in the file, not the actual result
        $db->getReference(__FUNCTION__)->shallow()->getSnapshot();

        $this->assertTrue($check);
    }

    #[Test]
    public function itSupportsOverridingTheDefaultFirestoreDatabase(): void
    {
        $firestore = self::$factory
            ->withFirestoreDatabase(__FUNCTION__)
            ->createFirestore();

        $db = $firestore->database();
        $name = $db->collection('irrelevant')->name();

        $this->assertStringContainsString(__FUNCTION__, $name);
    }

    #[Test]
    public function itSupportsAdditionalFirestoreConfig(): void
    {
        $firestore = self::$factory
            ->withFirestoreClientConfig(['database' => __FUNCTION__])
            ->createFirestore();

        $db = $firestore->database();
        $name = $db->collection('irrelevant')->name();

        $this->assertStringContainsString(__FUNCTION__, $name);
    }
}
