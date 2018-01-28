<?php

namespace Kreait\Firebase\Tests\Unit;

use Firebase\Auth\Token\Domain\Generator;
use Firebase\Auth\Token\Domain\Verifier;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Tests\UnitTestCase;
use Lcobucci\JWT\Token;

class AuthTest extends UnitTestCase
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
     * @var Generator
     */
    private $tokenGenerator;

    /**
     * @var Verifier
     */
    private $idTokenVerifier;

    /**
     * @var Auth
     */
    private $auth;

    protected function setUp()
    {
        $this->tokenGenerator = $this->createMock(Generator::class);
        $this->idTokenVerifier = $this->createMock(Verifier::class);
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
        $token = $this->createMock(Token::class);

        $this->tokenGenerator
            ->expects($this->once())
            ->method('createCustomToken')
            ->willReturn($token);

        $this->assertSame($token, $this->auth->createCustomToken('uid'));
    }

    public function testVerifyIdToken()
    {
        $this->idTokenVerifier
            ->expects($this->once())
            ->method('verifyIdToken');

        $this->auth->verifyIdToken('some id token string');
    }
}
