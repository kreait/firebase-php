<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database;
use Kreait\Firebase\Database\ApiClient;
use Kreait\Firebase\Database\RuleSet;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
class DatabaseTest extends UnitTestCase
{
    private $apiClient;

    /** @var Uri */
    private $uri;

    /** @var Database */
    private $database;

    protected function setUp(): void
    {
        $this->uri = new Uri('https://database-uri.tld');
        $this->apiClient = $this->createMock(ApiClient::class);

        $this->database = new Database($this->uri, $this->apiClient);
    }

    public function testGetReference()
    {
        $this->assertSame('any', \trim($this->database->getReference('any')->getUri()->getPath(), '/'));
    }

    public function testGetRootReference()
    {
        $this->assertSame('/', $this->database->getReference()->getUri()->getPath());
    }

    public function testGetReferenceWithInvalidPath()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->database->getReference('#');
    }

    public function testGetReferenceFromUrl()
    {
        $url = 'https://database-uri.tld/foo/bar';

        $this->assertSame($url, (string) $this->database->getReferenceFromUrl($url)->getUri());
    }

    public function testGetReferenceFromNonMatchingUrl()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->database->getReferenceFromUrl('http://non-matching.tld');
    }

    public function testGetRuleSet()
    {
        $this->apiClient
            ->method('get')
            ->with($this->uri->withPath('.settings/rules'))
            ->willReturn($expected = RuleSet::default()->getRules());

        $ruleSet = $this->database->getRuleSet();

        $this->assertEquals($expected, $ruleSet->getRules());
    }
}
