<?php

namespace Kreait\Tests\Firebase\Unit\Auth;

use Kreait\Firebase\Auth\IdTokenVerifier;
use Kreait\Firebase\Exception\Auth\InvalidIdToken;
use Kreait\Tests\Firebase\Unit\UnitTestCase;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Token;

class IdTokenVerifierTest extends UnitTestCase
{
    /**
     * @var IdTokenVerifier
     */
    private $verifier;

    protected function setUp()
    {
        $serviceAccount = $this->createServiceAccountMock();
        $this->verifier = new IdTokenVerifier($serviceAccount);
    }

    /**
     * @param Token $token
     *
     * @dataProvider validTokenProvider
     */
    public function testItSucceedsWithAValidToken($token)
    {
        $this->verifier->verify($token);

        $this->assertTrue($noExceptionWasThrown = true);
    }

    /**
     * @param string $token
     *
     * @dataProvider validTokenStringProvider
     */
    public function testItCanHandlAStringTokens($token)
    {
        $this->verifier->verify($token);

        $this->assertTrue($noExceptionWasThrown = true);
    }

    /**
     * @param Token $token
     * @param string $messagePattern
     *
     * @dataProvider invalidTokenProvider
     */
    public function testInvalidTokenResultsInException(Token $token, $messagePattern)
    {
        try {
            $this->verifier->verify($token);
        } catch (InvalidIdToken $e) {
            $this->assertRegExp($messagePattern, $e->getMessage());
            $this->assertSame($token, $e->getToken());
        }
    }

    public function validTokenProvider()
    {
        return [
            [(new Builder())
                ->setExpiration(time() + 1800)
                ->setIssuedAt(time() - 10)
                ->setAudience('project')
                ->setIssuer('https://securetoken.google.com/project')
                ->getToken(),
            ],
        ];
    }

    public function validTokenStringProvider()
    {
        return [
            [(string) (new Builder())
                ->setExpiration(time() + 1800)
                ->setIssuedAt(time() - 10)
                ->setAudience('project')
                ->setIssuer('https://securetoken.google.com/project')
                ->getToken(),
            ],
        ];
    }

    public function invalidTokenProvider()
    {
        $builder = new Builder();

        return [
            'no_exp_claim' => [
                $builder->getToken(),
                '/exp.*missing/'
            ],

            'expired' => [
                $builder
                    ->setExpiration(time() - 10)
                    ->getToken(),
                '/expired/',
            ],
            'no_iat_claim' => [
                $builder
                    ->setExpiration(time() + 1800)
                    ->getToken(),
                '/iat.*missing/',
            ],
            'not_yet_issued' => [
                $builder
                    ->setExpiration(time() + 1800)
                    ->setIssuedAt(time() + 1800)
                    ->getToken(),
                '/future/',
            ],
            'no_iss_claim' => [
                $builder
                    ->setExpiration(time() + 1800)
                    ->setIssuedAt(time() - 10)
                    ->getToken(),
                '/iss.*missing/',
            ],
            'invalid_issuer' => [
                $builder
                    ->setExpiration(time() + 1800)
                    ->setIssuedAt(time() - 10)
                    ->setIssuer('invalid_issuer')
                    ->getToken(),
                '/invalid.*issuer/',
            ],
            'no_aud_claim' => [
                $builder
                    ->setExpiration(time() + 1800)
                    ->setIssuedAt(time() - 10)
                    ->setIssuer(sprintf(IdTokenVerifier::ISSUER_FORMAT, 'project'))
                    ->getToken(),
                '/aud.*missing/',
            ],
            'no_audience' => [
                $builder
                    ->setExpiration(time() + 1800)
                    ->setIssuedAt(time() - 10)
                    ->setIssuer(sprintf(IdTokenVerifier::ISSUER_FORMAT, 'project'))
                    ->setAudience('audience')
                    ->getToken(),
                '/invalid.*audience/',
            ],
        ];
    }
}
