<?php

namespace Tests\Firebase;

use Firebase\Database;
use Firebase\Database\ApiClient;
use Firebase\Database\Reference;
use Firebase\Exception\InvalidArgumentException;
use Firebase\Http\Auth;
use GuzzleHttp\Psr7\Uri;
use Tests\FirebaseTestCase;

class DatabaseTest extends FirebaseTestCase
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

    public function testWithCustomAuth()
    {
        $auth = $this->createMock(Auth::class);

        $this->assertInstanceOf(Database::class, $this->database->withCustomAuth($auth));
    }

    public function testGetReference()
    {
        $this->assertInstanceOf(Reference::class, $this->database->getReference('any'));
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
}
