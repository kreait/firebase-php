<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database;
use Kreait\Firebase\Database\ApiClient;
use Kreait\Firebase\Database\RuleSet;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Tests\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 */
final class DatabaseTest extends UnitTestCase
{
    /** @var ApiClient|MockObject */
    private $apiClient;

    private Uri $uri;

    private Database $database;

    protected function setUp(): void
    {
        $this->uri = new Uri('https://database-uri.tld');
        $this->apiClient = $this->createMock(ApiClient::class);

        $this->database = new Database($this->uri, $this->apiClient);
    }

    public function testGetReference(): void
    {
        $this->assertSame('any', $this->database->getReference('any')->getPath());
    }

    public function testGetRootReference(): void
    {
        $this->assertSame('/', $this->database->getReference()->getUri()->getPath());
    }

    public function testGetReferenceWithInvalidPath(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->database->getReference('#');
    }

    public function testGetReferenceFromUrl(): void
    {
        $url = 'https://database-uri.tld/foo/bar';

        $this->assertSame($url, (string) $this->database->getReferenceFromUrl($url)->getUri());
    }

    public function testGetReferenceFromNonMatchingUrl(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->database->getReferenceFromUrl('http://non-matching.tld');
    }

    public function testGetRuleSet(): void
    {
        $this->apiClient
            ->method('get')
            ->with($this->uri->withPath('/.settings/rules'))
            ->willReturn($expected = RuleSet::default()->getRules())
        ;

        $ruleSet = $this->database->getRuleSet();

        $this->assertEquals($expected, $ruleSet->getRules());
    }
}
