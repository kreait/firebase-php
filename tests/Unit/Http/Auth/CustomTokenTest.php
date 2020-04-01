<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Http\Auth;

use GuzzleHttp\Psr7;
use Kreait\Firebase\Http\Auth\CustomToken;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
class CustomTokenTest extends UnitTestCase
{
    /**
     * @var Psr7\Request
     */
    private $request;

    protected function setUp(): void
    {
        $this->request = new Psr7\Request('GET', 'http://domain.tld');
    }

    /**
     * @param string $uid
     * @param array $claims
     *
     * @dataProvider customTokenProvider
     */
    public function testAuthenticateRequest($uid, $claims, array $expectedQueryParams)
    {
        $auth = new CustomToken($uid, $claims);

        $authenticated = $auth->authenticateRequest($this->request);

        $this->assertNotSame($this->request, $authenticated);

        $queryParams = Psr7\parse_query($authenticated->getUri()->getQuery());

        $this->assertArrayHasKey('auth_variable_override', $queryParams);
        $this->assertJson($queryParams['auth_variable_override']);

        $this->assertEquals($expectedQueryParams, \json_decode($queryParams['auth_variable_override'], true));
    }

    public function customTokenProvider()
    {
        $uid = 'uid';

        $emptyClaims = [];

        $claims = [
            'string' => 'string',
            'int' => 1337,
            'float' => 1337.37,
            'bool_true' => true,
            'bool_false' => false,
            'null' => null,
        ];

        $expectedClaims = [
            'uid' => $uid,
            'string' => 'string',
            'int' => 1337,
            'float' => 1337.37,
            'bool_true' => true,
            'bool_false' => false,
        ];

        return [
            'without_claims' => ['uid', $emptyClaims, ['uid' => $uid]],
            'with_claims' => ['uid', $claims, $expectedClaims],
        ];
    }
}
