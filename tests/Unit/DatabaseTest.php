<?php

namespace Kreait\Firebase\Tests\Unit;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database;
use Kreait\Firebase\Database\ApiClient;
use Kreait\Firebase\Database\Reference;
use Kreait\Firebase\Database\RuleSet;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Tests\UnitTestCase;

class DatabaseTest extends UnitTestCase
{
    /**
     * @var Uri
     */
    private $uri;

    /**
     * @var ApiClient|\PHPUnit_Framework_MockObject_MockObject
     */
    private $apiClient;

    /**
     * @var Database
     */
    private $database;

    protected function setUp()
    {
        $this->uri = new Uri('https://database-uri.tld');
        $this->apiClient = $this->createMock(ApiClient::class);

        $this->database = new Database($this->uri, $this->apiClient);
    }

    public function testGetReference()
    {
        $this->assertInstanceOf(Reference::class, $this->database->getReference('any'));
    }

    public function testGetReferenceWithInvalidPath()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->database->getReference('#');
    }

    public function testGetReferenceFromUrl()
    {
        $this->assertInstanceOf(
            Reference::class,
            $this->database->getReferenceFromUrl('https://database-uri.tld/foo/bar')
        );
    }

    public function testGetReferenceFromInvalidUrl()
    {
        $this->expectException(InvalidArgumentException::class);

        // We don't test any possibly invalid URL, this is already handled by the HTTP client library
        $this->database->getReferenceFromUrl(false);
    }

    public function testGetReferenceFromNonMatchingUrl()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->database->getReferenceFromUrl('http://non-matching.tld');
    }

    public function testGetRules()
    {
        $this->apiClient->expects($this->once())
            ->method('get')
            ->with($this->uri->withPath('.settings/rules'))
            ->willReturn($expected = RuleSet::default()->getRules());

        $ruleSet = $this->database->getRules();

        $this->assertEquals($expected, $ruleSet->getRules());
    }

    public function testSetRules()
    {
        $ruleSet = RuleSet::default();

        $this->apiClient->expects($this->once())
            ->method('set')
            ->with($this->uri->withPath('.settings/rules'), $ruleSet);

        $this->database->updateRules($ruleSet);
    }
}
