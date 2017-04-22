<?php

namespace Kreait\Tests\Firebase;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database;
use Kreait\Firebase\Database\ApiClient;
use Kreait\Firebase\Database\Reference;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Http\Auth;
use Kreait\Tests\FirebaseTestCase;

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
        /** @var \PHPUnit_Framework_MockObject_MockObject|Auth $auth */
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
