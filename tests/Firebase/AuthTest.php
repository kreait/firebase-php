<?php

namespace Kreait\Tests\Firebase;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Kreait\Firebase\Auth;
use Kreait\Tests\FirebaseTestCase;
use Lcobucci\JWT\Token;

class AuthTest extends FirebaseTestCase
{
    /**
     * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $httpClient;

    /**
     * @var Auth\ApiClient
     */
    private $apiClient;

    /**
     * @var Auth\CustomTokenGenerator
     */
    private $tokenGenerator;

    /**
     * @var Auth
     */
    private $auth;

    protected function setUp()
    {
        $this->tokenGenerator = new Auth\CustomTokenGenerator($this->createServiceAccountMock());
        $this->httpClient = $this->createMock(Client::class);
        $this->apiClient = new Auth\ApiClient($this->httpClient);
        $this->auth = new Auth($this->apiClient, $this->tokenGenerator);
    }

    public function testGetApiClient()
    {
        $this->assertSame($this->apiClient, $this->auth->getApiClient());
    }

    public function testCreateCustomToken()
    {
        $this->assertInstanceOf(Token::class, $this->auth->createCustomToken('uid'));
    }

    public function testCreateUser()
    {
        $this->markTestSkipped('We have to test this with integration tests.');
        $this->assertInstanceOf(Auth\User::class, $this->auth->getUser('uid'));
    }
}
