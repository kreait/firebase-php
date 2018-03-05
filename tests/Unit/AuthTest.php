<?php

namespace Kreait\Firebase\Tests\Unit;

use Firebase\Auth\Token\Domain\Generator;
use Firebase\Auth\Token\Domain\Verifier;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Auth\ApiClient;
use Kreait\Firebase\Tests\UnitTestCase;

class AuthTest extends UnitTestCase
{
    /**
     * @var ApiClient
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
        $this->apiClient = $this->createMock(ApiClient::class);
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
            ->method('createCustomToken');

        $this->auth->createCustomToken('uid');
    }

    public function testVerifyIdToken()
    {
        $this->idTokenVerifier
            ->expects($this->once())
            ->method('verifyIdToken');

        $this->auth->verifyIdToken('some id token string');
    }
}
