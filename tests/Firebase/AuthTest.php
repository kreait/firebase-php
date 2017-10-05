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
     * @var Auth\CustomTokenGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tokenGenerator;

    /**
     * @var Auth\IdTokenVerifier|\PHPUnit_Framework_MockObject_MockObject
     */
    private $idTokenVerifier;

    /**
     * @var Auth
     */
    private $auth;

    protected function setUp()
    {
        $this->tokenGenerator = $this->createMock(Auth\CustomTokenGenerator::class);
        $this->idTokenVerifier = $this->createMock(Auth\IdTokenVerifier::class);
        $this->httpClient = $this->createMock(Client::class);
        $this->apiClient = new Auth\ApiClient($this->httpClient);
        $this->auth = new Auth($this->apiClient, $this->tokenGenerator, $this->idTokenVerifier);
    }

    public function testGetApiClient()
    {
        $this->assertSame($this->apiClient, $this->auth->getApiClient());
    }

    public function testCreateCustomToken()
    {
        $this->tokenGenerator
            ->expects($this->once())
            ->method('create');
        $this->assertInstanceOf(Token::class, $this->auth->createCustomToken('uid'));
    }

    public function testVerifyIdToken()
    {
        $this->idTokenVerifier
            ->expects($this->once())
            ->method('verify');

        $this->auth->verifyIdToken('some id token string');
    }

    public function testCreateUser()
    {
        $this->markTestSkipped('We have to test this with integration tests.');
        $this->assertInstanceOf(Auth\User::class, $this->auth->getUser('uid'));
    }
}
