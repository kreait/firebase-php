<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database;

use InvalidArgumentException;
use Kreait\Firebase\Database\UrlBuilder;
use Kreait\Firebase\Tests\UnitTestCase;
use Kreait\Firebase\Util;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
final class UrlBuilderTest extends UnitTestCase
{
    protected function tearDown(): void
    {
        Util::rmenv('FIREBASE_DATABASE_EMULATOR_HOST');
    }

    /**
     * @param non-empty-string $url
     */
    #[DataProvider('invalidUrls')]
    #[Test]
    public function withInvalidUrl(string $url): void
    {
        $this->expectException(InvalidArgumentException::class);
        UrlBuilder::create($url);
    }

    /**
     * @return array<non-empty-string, array<non-empty-string>>
     */
    public static function invalidUrls(): array
    {
        return [
            'wrong scheme' => ['http://domain.tld'],
            'no scheme' => ['domain.tld'],
        ];
    }

    /**
     * @param non-empty-string $baseUrl
     * @param array<string, string> $queryParams
     * @param non-empty-string $expected
     */
    #[DataProvider('realUrls')]
    public function getGetUrl(string $baseUrl, string $path, array $queryParams, string $expected): void
    {
        $url = UrlBuilder::create($baseUrl)->getUrl($path, $queryParams);

        $this->assertSame($expected, $url);
    }

    /**
     * @param non-empty-string $emulatorHost
     * @param non-empty-string $baseUrl
     * @param array<string, string> $queryParams
     * @param non-empty-string $expected
     */
    #[DataProvider('emulatedUrls')]
    #[Test]
    public function emulated(string $emulatorHost, string $baseUrl, string $path, array $queryParams, string $expected): void
    {
        Util::putenv('FIREBASE_DATABASE_EMULATOR_HOST', $emulatorHost);
        $url = UrlBuilder::create($baseUrl)->getUrl($path, $queryParams);

        $this->assertSame($expected, $url);
    }

    /**
     * @return array<array-key, array<array-key, string|array<string, string>>>
     */
    public static function realUrls(): array
    {
        $baseUrl = 'https://project.region.db.tld';

        return [
            'empty path, empty query' => [
                $baseUrl,
                '',
                [],
                $baseUrl.'/',
            ],
            'path without trailing slash, empty query' => [
                $baseUrl,
                '/path/to/child',
                [],
                $baseUrl.'/path/to/child',
            ],
            'path with trailing slash, empty query' => [
                $baseUrl,
                '/path/to/child/',
                [],
                $baseUrl.'/path/to/child',
            ],
            'path without trailing slash, non-empty query' => [
                $baseUrl,
                '/path/to/child',
                ['one' => 'two', 'three' => 'four'],
                $baseUrl.'/path/to/child?one=two&three=four',
            ],
            'path with trailing slash, non-empty query' => [
                $baseUrl,
                '/path/to/child/',
                ['one' => 'two', 'three' => 'four'],
                $baseUrl.'/path/to/child?one=two&three=four',
            ],
            'empty path, non-empty query' => [
                $baseUrl,
                '',
                ['one' => 'two', 'three' => 'four'],
                $baseUrl.'/?one=two&three=four',
            ],
        ];
    }

    /**
     * @return array<array-key, array<array-key, string|array<string, string>>>
     */
    public static function emulatedUrls(): array
    {
        $namespace = 'namespace';
        $baseUrl = 'https://'.$namespace.'.db.tld';
        $emulatorHost = 'localhost:9000';

        return [
            'empty path, empty query' => [
                $emulatorHost,
                $baseUrl,
                '',
                [],
                'http://'.$emulatorHost.'/?ns=namespace',
            ],
            'path without trailing slash, empty query' => [
                $emulatorHost,
                $baseUrl,
                '/path/to/child',
                [],
                'http://'.$emulatorHost.'/path/to/child?ns=namespace',
            ],
            'path with trailing slash, empty query' => [
                $emulatorHost,
                $baseUrl,
                '/path/to/child/',
                [],
                'http://'.$emulatorHost.'/path/to/child?ns=namespace',
            ],
            'path without trailing slash, non-empty query' => [
                $emulatorHost,
                $baseUrl,
                '/path/to/child',
                ['one' => 'two', 'three' => 'four'],
                'http://'.$emulatorHost.'/path/to/child?ns=namespace&one=two&three=four',
            ],
            'path with trailing slash, non-empty query' => [
                $emulatorHost,
                $baseUrl,
                '/path/to/child/',
                ['one' => 'two', 'three' => 'four'],
                'http://'.$emulatorHost.'/path/to/child?ns=namespace&one=two&three=four',
            ],
            'empty path, non-empty query' => [
                $emulatorHost,
                $baseUrl,
                '',
                ['one' => 'two', 'three' => 'four'],
                'http://'.$emulatorHost.'/?ns=namespace&one=two&three=four',
            ],
        ];
    }
}
