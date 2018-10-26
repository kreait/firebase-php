<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Auth;

use DateTimeImmutable;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Kreait\Firebase\Auth\IdTokenVerifier;
use Kreait\Firebase\Exception\InvalidToken;
use Kreait\Firebase\Exception\RuntimeException;
use Kreait\Firebase\Tests\Util\FixedClock;
use Kreait\Firebase\Tests\Util\TestSigner;
use Lcobucci\JWT\Builder;
use PHPUnit\Framework\TestCase;

class IdTokenVerifierTest extends TestCase
{
    /**
     * @var string
     */
    protected $projectId;

    /**
     * @var string
     */
    protected $validKeyId;

    /**
     * @var string
     */
    protected $validKey;

    /**
     * @var MockHandler
     */
    protected $mockHandler;

    /**
     * @var FixedClock
     */
    protected $clock;

    /**
     * @var TestSigner
     */
    protected $signer;

    /**
     * @var IdTokenVerifier
     */
    protected $verifier;

    protected function setUp()
    {
        $this->projectId = 'project-id';
        $this->validKeyId = 'key_id';
        $this->validKey = 'key';

        $this->mockHandler = new MockHandler();

        $this->clock = $this->fixedClock();
        $this->signer = new TestSigner();

        $client = new Client(['handler' => $this->mockHandler]);

        $this->verifier = new IdTokenVerifier('project-id', $client, $this->signer, $this->clock);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function it_verifies_a_valid_token()
    {
        $this->mockHandler->append($this->apiResponse());

        $now = $this->clock->now();

        $token = (new Builder())
            ->setExpiration($now->modify('+1 hour')->getTimestamp())
            ->setIssuedAt($now->modify('-1 hour')->getTimestamp())
            ->set('auth_time', $now->modify('-1 hour')->getTimestamp())
            ->setIssuer(sprintf(IdTokenVerifier::ISSUER_FORMAT, $this->projectId))
            ->setHeader('kid', $this->validKeyId)
            ->sign($this->signer, $this->validKey)
            ->getToken();

        $this->verifier->verify($token);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function it_verifies_an_expired_token_with_leeway()
    {
        $this->mockHandler->append($this->apiResponse());

        $now = $this->clock->now();

        $token = (new Builder())
            ->setExpiration($now->modify('-10 seconds')->getTimestamp())
            ->setIssuedAt($now->modify('-1 hour')->getTimestamp())
            ->set('auth_time', $now->modify('-1 hour')->getTimestamp())
            ->setIssuer(sprintf(IdTokenVerifier::ISSUER_FORMAT, $this->projectId))
            ->setHeader('kid', $this->validKeyId)
            ->sign($this->signer, $this->validKey)
            ->getToken();

        $this->verifier->verify($token, '10 seconds');
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function it_verifies_a_token_issued_in_the_future_with_leeway()
    {
        $this->mockHandler->append($this->apiResponse());

        $now = $this->clock->now();

        $token = (new Builder())
            ->setExpiration($now->modify('+1 hour')->getTimestamp())
            ->setIssuedAt($now->modify('+10 seconds')->getTimestamp())
            ->set('auth_time', $now->modify('+10 seconds')->getTimestamp())
            ->setIssuer(sprintf(IdTokenVerifier::ISSUER_FORMAT, $this->projectId))
            ->setHeader('kid', $this->validKeyId)
            ->sign($this->signer, $this->validKey)
            ->getToken();

        $this->verifier->verify($token, '10 seconds');
    }

    /**
     * @test
     */
    public function it_handles_api_errors()
    {
        $this->mockHandler->append(new Response(500));
        $now = $this->clock->now();

        $token = (new Builder())
            ->setExpiration($now->modify('+1 hour')->getTimestamp())
            ->setIssuedAt($now->modify('-1 hour')->getTimestamp())
            ->set('auth_time', $now->modify('-1 hour')->getTimestamp())
            ->setIssuer(sprintf(IdTokenVerifier::ISSUER_FORMAT, $this->projectId))
            ->setHeader('kid', $this->validKeyId)
            ->sign($this->signer, $this->validKey)
            ->getToken();

        $this->expectException(RuntimeException::class);
        $this->verifier->verify($token);
    }

    /**
     * @test
     * @dataProvider invalidTokens
     */
    public function it_rejects_an_invalid_token_when($token)
    {
        $this->mockHandler->append($this->apiResponse());

        $this->expectException(InvalidToken::class);
        $this->verifier->verify($token);
    }

    public function invalidTokens()
    {
        $now = $this->fixedClock()->now();

        return [
            'it is not a token' => ['this is not a token'],
            'it has no "exp" claim' => [
                (new Builder())->getToken(),
            ],
            'it is expired' => [
                (new Builder())
                    ->setExpiration($now->modify('-10 seconds')->getTimestamp())
                    ->getToken(),
            ],
            'it has no "iat" claim' => [
                (new Builder())
                    ->setExpiration($now->modify('+1 hour')->getTimestamp())
                    ->getToken(),
            ],
            'it is issued in the future' => [
                (new Builder())
                    ->setExpiration($now->modify('+1 hour')->getTimestamp())
                    ->setIssuedAt($now->modify('+1 minute')->getTimestamp())
                    ->getToken(),
            ],
            'it has no "auth_time" claim' => [
                (new Builder())
                    ->setExpiration($now->modify('+1 hour')->getTimestamp())
                    ->setIssuedAt($now->modify('-1 hour')->getTimestamp())
                    ->getToken(),
            ],
            'it is auth_timed in the future' => [
                (new Builder())
                    ->setExpiration($now->modify('+1 hour')->getTimestamp())
                    ->setIssuedAt($now->modify('-1 hour')->getTimestamp())
                    ->set('auth_time', $now->modify('+1 minute')->getTimestamp())
                    ->getToken(),
            ],
            'it has no issuer' => [
                (new Builder())
                    ->setExpiration($now->modify('+1 hour')->getTimestamp())
                    ->setIssuedAt($now->modify('-1 hour')->getTimestamp())
                    ->set('auth_time', $now->modify('-1 hour')->getTimestamp())
                    ->getToken(),
            ],
            'it has an invalid issuer' => [
                (new Builder())
                    ->setExpiration($now->modify('+1 hour')->getTimestamp())
                    ->setIssuedAt($now->modify('-1 hour')->getTimestamp())
                    ->set('auth_time', $now->modify('-1 hour')->getTimestamp())
                    ->setIssuer('invalid_issuer')
                    ->getToken(),
            ],
            'it has no "kid" header' => [
                (new Builder())
                    ->setExpiration($now->modify('+1 hour')->getTimestamp())
                    ->setIssuedAt($now->modify('-1 hour')->getTimestamp())
                    ->set('auth_time', $now->modify('-1 hour')->getTimestamp())
                    ->setIssuer('https://securetoken.google.com/project-id')
                    ->getToken(),
            ],
            'it is unsigned' => [
                (new Builder())
                    ->setExpiration($now->modify('+1 hour')->getTimestamp())
                    ->setIssuedAt($now->modify('-1 hour')->getTimestamp())
                    ->set('auth_time', $now->modify('-1 hour')->getTimestamp())
                    ->setIssuer('https://securetoken.google.com/project-id')
                    ->setHeader('kid', 'key_id')
                    ->getToken(),
            ],
            'it is not signed by the given key' => [
                (new Builder())
                    ->setExpiration($now->modify('+1 hour')->getTimestamp())
                    ->setIssuedAt($now->modify('-1 hour')->getTimestamp())
                    ->set('auth_time', $now->modify('-1 hour')->getTimestamp())
                    ->setIssuer('https://securetoken.google.com/project-id')
                    ->setHeader('kid', 'key_id')
                    ->sign(new TestSigner(), 'invalid_key')
                    ->getToken(),
            ],
            'it has an invalid signature' => [
                (new Builder())
                    ->setExpiration($now->modify('+1 hour')->getTimestamp())
                    ->setIssuedAt($now->modify('-1 hour')->getTimestamp())
                    ->set('auth_time', $now->modify('-1 hour')->getTimestamp())
                    ->setIssuer('https://securetoken.google.com/project-id')
                    ->setHeader('kid', 'invalid_key_id')
                    ->sign(new TestSigner(), 'invalid_key')
                    ->getToken(),
            ],
        ];
    }

    private function fixedClock()
    {
        return new FixedClock(new DateTimeImmutable('2018-10-05 13:37'));
    }

    private function apiResponse(): Response
    {
        return new Response(200, [], json_encode([$this->validKeyId => $this->validKey]));
    }
}
