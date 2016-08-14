<?php

namespace Tests\Firebase\V2\Http\Auth;

use Firebase\V2\Http\Auth\CustomToken;
use GuzzleHttp\Psr7;
use Tests\FirebaseTestCase;

class CustomTokenTest extends FirebaseTestCase
{
    /**
     * @var Psr7\Request
     */
    private $request;

    protected function setUp()
    {
        $this->request = new Psr7\Request('GET', 'http://domain.tld');
    }

    /**
     * @param string $databaseSecret
     * @param string $uid
     * @param string $claims
     *
     * @dataProvider customTokenDataProvider
     */
    public function testAuthenticateRequest($databaseSecret, $uid, $claims)
    {
        $auth = new CustomToken($databaseSecret, $uid, $claims);

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
