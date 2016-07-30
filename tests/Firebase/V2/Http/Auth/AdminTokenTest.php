<?php

namespace Tests\Firebase\V2\Http\Auth;

use Firebase\V2\Http\Auth\AdminToken;
use Tests\FirebaseTestCase;
use GuzzleHttp\Psr7;

class AdminTokenTest extends FirebaseTestCase
{
    /**
     * @var Psr7\Request
     */
    private $request;

    protected function setUp()
    {
        $this->request = new Psr7\Request('GET', 'http://domain.tld');
    }

    public function testAuthenticateRequest()
    {
        $auth = new AdminToken('database_secret');

        $authenticated = $auth->authenticateRequest($this->request);

        $this->assertNotSame($this->request, $authenticated);

        $queryParams = Psr7\parse_query($authenticated->getUri()->getQuery());

        $this->assertArrayHasKey('auth', $queryParams);
    }

    public function customTokenDataProvider()
    {
        return [
            'without_claims' => ['secret', 'uid', []],
            'with_claims' => ['secret', 'uid', ['foo' => 'bar']]
        ];
    }
}
