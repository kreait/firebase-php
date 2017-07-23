<?php

namespace Kreait\Tests\Firebase\Auth;

use Kreait\Firebase\Auth\IdTokenVerifier;
use Kreait\Firebase\Exception\Auth\InvalidIdToken;
use Kreait\Tests\FirebaseTestCase;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Token;

class IdTokenVerifierTest extends FirebaseTestCase
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
        $this->assertInstanceOf(Token::class, $this->verifier->verify($token));
    }

    /**
     * @param string $token
     *
     * @dataProvider validTokenStringProvider
     */
    public function testItCanHandlAStringTokens($token)
    {
        $this->assertInstanceOf(Token::class, $this->verifier->verify($token));
    }

    /**
     * @param Token $token
     * @param string $exception
     * @dataProvider invalidTokenProvider
     */
    public function testInvalidTokenResultsInException(Token $token, $exception)
    {
        $this->expectException($exception);

        $this->verifier->verify($token);
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
                InvalidIdToken::class,
            ],
            'expired' => [
                $builder
                    ->setExpiration(time() - 10)
                    ->getToken(),
                InvalidIdToken::class,
            ],
            'no_iat_claim' => [
                $builder
                    ->setExpiration(time() + 1800)
                    ->getToken(),
                InvalidIdToken::class,
            ],
            'not_yet_issued' => [
                $builder
                    ->setExpiration(time() + 1800)
                    ->setIssuedAt(time() + 1800)
                    ->getToken(),
                InvalidIdToken::class,
            ],
            'no_iss_claim' => [
                $builder
                    ->setExpiration(time() + 1800)
                    ->setIssuedAt(time() - 10)
                    ->getToken(),
                InvalidIdToken::class,
            ],
            'invalid_issuer' => [
                $builder
                    ->setExpiration(time() + 1800)
                    ->setIssuedAt(time() - 10)
                    ->setIssuer('invalid_issuer')
                    ->getToken(),
                InvalidIdToken::class,
            ],
            'no_aud_claim' => [
                $builder
                    ->setExpiration(time() + 1800)
                    ->setIssuedAt(time() - 10)
                    ->setIssuer('project')
                    ->getToken(),
                InvalidIdToken::class,
            ],
            'no_audience' => [
                $builder
                    ->setExpiration(time() + 1800)
                    ->setIssuedAt(time() - 10)
                    ->setIssuer('project')
                    ->setAudience('project')
                    ->getToken(),
                InvalidIdToken::class,
            ],
        ];
    }
}
