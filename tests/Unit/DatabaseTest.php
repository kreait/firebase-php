<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database;
use Kreait\Firebase\Database\ApiClient;
use Kreait\Firebase\Database\RuleSet;
use Kreait\Firebase\Database\UrlBuilder;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Tests\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 */
final class DatabaseTest extends UnitTestCase
{
    private ApiClient&MockObject $apiClient;
    private string $url;
    private Uri $uri;
    private Database $database;

    protected function setUp(): void
    {
        $this->url = 'https://database.firebaseio.tld';
        $this->uri = new Uri($this->url);
        $this->apiClient = $this->createMock(ApiClient::class);

        $this->database = new Database($this->uri, $this->apiClient, UrlBuilder::create($this->url));
    }

    /**
     * @test
     */
    public function getReference(): void
    {
        $this->assertSame('any', $this->database->getReference('any')->getPath());
    }

    /**
     * @test
     */
    public function getRootReference(): void
    {
        $this->assertSame('/', $this->database->getReference()->getUri()->getPath());
    }

    /**
     * @test
     */
    public function getReferenceWithInvalidPath(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->database->getReference('#');
    }

    /**
     * @test
     */
    public function getReferenceFromUrl(): void
    {
        $url = $this->url.'/foo/bar';

        $this->assertSame($url, (string) $this->database->getReferenceFromUrl($url)->getUri());
    }

    /**
     * @test
     */
    public function getReferenceFromNonMatchingUrl(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->database->getReferenceFromUrl('http://non-matching.tld');
    }

    /**
     * @test
     */
    public function getRuleSet(): void
    {
        $this->apiClient
            ->method('get')
            ->with('/.settings/rules')
            ->willReturn($expected = RuleSet::default()->getRules())
        ;

        $ruleSet = $this->database->getRuleSet();

        $this->assertEqualsCanonicalizing($expected, $ruleSet->getRules());
    }
}
