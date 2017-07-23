<?php

namespace Kreait\Firebase\Auth;

use Kreait\Firebase\Exception\Auth\InvalidIdToken;
use Kreait\Firebase\ServiceAccount;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Token;

class IdTokenVerifier
{
    /**
     * @var ServiceAccount
     */
    private $serviceAccount;

    public function __construct(ServiceAccount $serviceAccount)
    {
        $this->serviceAccount = $serviceAccount;
    }

    public function verify($token): Token
    {
        $token = $token instanceof Token ? $token : (new Parser())->parse($token);

        $this->verifyExpiry($token);
        $this->verifyIssuedAt($token);
        $this->verifyIssuer($token);
        $this->verifyAudience($token);

        return $token;
    }

    private function verifyExpiry(Token $token)
    {
        if (!$token->hasClaim('exp')) {
            throw new InvalidIdToken('The claim "exp" is missing.');
        }

        if ($token->isExpired()) {
            throw new InvalidIdToken('The token is expired.');
        }
    }

    private function verifyIssuedAt(Token $token)
    {
        if (!$token->hasClaim('iat')) {
            throw new InvalidIdToken('The claim "iat" is missing.');
        }

        if ($token->getClaim('iat') > time()) {
            throw new InvalidIdToken('This token has been issued in the future.');
        }
    }

    private function verifyAudience(Token $token)
    {
        if (!$token->hasClaim('aud')) {
            throw new InvalidIdToken('The claim "aud" is missing.');
        }

        if ($token->getClaim('aud') !== $this->serviceAccount->getProjectId()) {
            throw new InvalidIdToken('This token has an invalid audience.');
        }
    }

    private function verifyIssuer(Token $token)
    {
        if (!$token->hasClaim('iss')) {
            throw new InvalidIdToken('The claim "iss" is missing.');
        }

        if ($token->getClaim('iss') !== sprintf('https://securetoken.google.com/%s', $this->serviceAccount->getProjectId())) {
            throw new InvalidIdToken('This token has an invalid issuer.');
        }
    }
}
