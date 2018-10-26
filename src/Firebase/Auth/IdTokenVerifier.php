<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Kreait\Firebase\Clock;
use Kreait\Firebase\Exception\ApiException;
use Kreait\Firebase\Exception\InvalidToken;
use Kreait\Firebase\Exception\RuntimeException;
use Kreait\Firebase\SystemClock;
use Kreait\Firebase\Util\Duration;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;

final class IdTokenVerifier implements TokenVerifier
{
    const KEYS_URL = 'https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com';
    const ISSUER_FORMAT = 'https://securetoken.google.com/%s';

    /**
     * @var string
     */
    private $projectId;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var Signer
     */
    private $signer;

    /**
     * @var Clock
     */
    private $clock;

    public function __construct(string $projectId, ClientInterface $client = null, Signer $signer = null, Clock $clock = null)
    {
        $this->projectId = $projectId;
        $this->client = $client ?: new Client();
        $this->signer = $signer ?: new Sha256();
        $this->clock = $clock ?: new SystemClock();
    }

    public function verify($token, $leeway = null)
    {
        try {
            $token = $token instanceof Token ? $token : (new Parser())->parse($token);
        } catch (\Throwable $e) {
            throw InvalidToken::because('The given value could not be parsed as a token: '.$e->getMessage());
        }

        $leeway = $leeway instanceof Duration ? $leeway : Duration::fromValue($leeway);

        $this->verifyExpiry($token, $leeway);
        $this->verifyIssuedAt($token, $leeway);
        $this->verifyAuthTime($token, $leeway);
        $this->verifyIssuer($token);
        $this->verifySignature($token);
    }

    private function verifyExpiry(Token $token, Duration $leeway)
    {
        if (!$token->hasClaim('exp')) {
            throw InvalidToken::because('The claim "exp" is missing.');
        }

        $now = $this->clock->now();
        $expiresAt = $now->setTimestamp($token->getClaim('exp'));
        $withSubtractedLeeway = $now->sub($leeway->asInterval());

        if ($withSubtractedLeeway > $expiresAt) {
            throw InvalidToken::because('The token is expired since '.$expiresAt->format(\DATE_ATOM));
        }
    }

    private function verifyIssuedAt(Token $token, Duration $leeway)
    {
        if (!$token->hasClaim('iat')) {
            throw InvalidToken::because('The claim "iat" is missing.');
        }

        $now = $this->clock->now();
        $issuedAt = $now->setTimestamp((int) $token->getClaim('iat'));
        $withAddedLeeway = $now->add($leeway->asInterval());

        if ($issuedAt > $withAddedLeeway) {
            throw InvalidToken::because('The token is issued in the future.');
        }
    }

    private function verifyAuthTime(Token $token, Duration $leeway)
    {
        if (!$token->hasClaim('auth_time')) {
            throw InvalidToken::because('The claim "auth_time" is missing.');
        }

        $now = $this->clock->now();
        $issuedAt = $now->setTimestamp((int) $token->getClaim('auth_time'));
        $withAddedLeeway = $now->add($leeway->asInterval());

        if ($issuedAt > $withAddedLeeway) {
            throw InvalidToken::because('The token has has been authenticated in the future.');
        }
    }

    private function verifyIssuer(Token $token)
    {
        if (!$token->hasClaim('iss')) {
            throw InvalidToken::because('The claim "iss" is missing.');
        }

        if ($token->getClaim('iss') !== sprintf(self::ISSUER_FORMAT, $this->projectId)) {
            throw InvalidToken::because('This token has an invalid issuer.');
        }
    }

    private function verifySignature(Token $token)
    {
        $key = $this->getKey($token);

        try {
            $isVerified = $token->verify($this->signer, $key);
        } catch (\Throwable $e) {
            throw InvalidToken::because("The token's signature is incorrect: {$e->getMessage()}".$e->getMessage());
        }

        if (!$isVerified) {
            throw InvalidToken::because('This token is not verified.');
        }
    }

    private function getKey(Token $token): string
    {
        if (!$token->hasHeader('kid')) {
            throw InvalidToken::because('The header "kid" is missing.');
        }

        $keyId = $token->getHeader('kid');

        try {
            $response = $this->client->request('GET', self::KEYS_URL);

            // In case the client is not using the default handler stack
            if (200 !== $response->getStatusCode()) {
                throw new RuntimeException('The request to '.self::KEYS_URL.' returned an unexpected response.');
            }
        } catch (RequestException $e) {
            throw ApiException::wrapRequestException($e);
        } catch (\Throwable $e) {
            throw new RuntimeException('Error while requesting the signing keys from Google: '.$e->getMessage(), $e->getCode(), $e);
        }

        $keys = json_decode((string) $response->getBody(), true);

        if (!($key = $keys[$keyId] ?? null)) {
            throw InvalidToken::because("The Token claims it has been signed by the key with ID {$keyId}, but Google doesn't know this key.");
        }

        return (string) $key;
    }
}
