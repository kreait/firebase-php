<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database;

use InvalidArgumentException;
use Iterator;
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

    public static function invalidUrls(): Iterator
    {
        yield 'wrong scheme' => ['http://example.com'];
        yield 'no scheme' => ['example.com'];
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

    public static function realUrls(): Iterator
    {
        $baseUrl = 'https://project.region.example.com';
        yield 'empty path, empty query' => [
            $baseUrl,
            '',
            [],
            $baseUrl.'/',
        ];
        yield 'path without trailing slash, empty query' => [
            $baseUrl,
            '/path/to/child',
            [],
            $baseUrl.'/path/to/child',
        ];
        yield 'path with trailing slash, empty query' => [
            $baseUrl,
            '/path/to/child/',
            [],
            $baseUrl.'/path/to/child',
        ];
        yield 'path without trailing slash, non-empty query' => [
            $baseUrl,
            '/path/to/child',
            ['one' => 'two', 'three' => 'four'],
            $baseUrl.'/path/to/child?one=two&three=four',
        ];
        yield 'path with trailing slash, non-empty query' => [
            $baseUrl,
            '/path/to/child/',
            ['one' => 'two', 'three' => 'four'],
            $baseUrl.'/path/to/child?one=two&three=four',
        ];
        yield 'empty path, non-empty query' => [
            $baseUrl,
            '',
            ['one' => 'two', 'three' => 'four'],
            $baseUrl.'/?one=two&three=four',
        ];
    }

    public static function emulatedUrls(): Iterator
    {
        $namespace = 'namespace';
        $baseUrl = 'https://'.$namespace.'.example.com';
        $emulatorHost = 'localhost:9000';
        yield 'empty path, empty query' => [
            $emulatorHost,
            $baseUrl,
            '',
            [],
            'http://'.$emulatorHost.'/?ns=namespace',
        ];
        yield 'path without trailing slash, empty query' => [
            $emulatorHost,
            $baseUrl,
            '/path/to/child',
            [],
            'http://'.$emulatorHost.'/path/to/child?ns=namespace',
        ];
        yield 'path with trailing slash, empty query' => [
            $emulatorHost,
            $baseUrl,
            '/path/to/child/',
            [],
            'http://'.$emulatorHost.'/path/to/child?ns=namespace',
        ];
        yield 'path without trailing slash, non-empty query' => [
            $emulatorHost,
            $baseUrl,
            '/path/to/child',
            ['one' => 'two', 'three' => 'four'],
            'http://'.$emulatorHost.'/path/to/child?ns=namespace&one=two&three=four',
        ];
        yield 'path with trailing slash, non-empty query' => [
            $emulatorHost,
            $baseUrl,
            '/path/to/child/',
            ['one' => 'two', 'three' => 'four'],
            'http://'.$emulatorHost.'/path/to/child?ns=namespace&one=two&three=four',
        ];
        yield 'empty path, non-empty query' => [
            $emulatorHost,
            $baseUrl,
            '',
            ['one' => 'two', 'three' => 'four'],
            'http://'.$emulatorHost.'/?ns=namespace&one=two&three=four',
        ];
    }
}
