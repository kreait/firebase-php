<?php

declare(strict_types=1);

namespace Kreait\Firebase\AppCheck;

use Firebase\JWT\CachedKeySet;
use Firebase\JWT\JWT;
use Kreait\Firebase\Exception\AppCheck\FailedToVerifyAppCheckToken;
use Kreait\Firebase\Exception\AppCheck\InvalidAppCheckToken;
use LogicException;
use UnexpectedValueException;

use function in_array;
use function str_starts_with;

/**
 * @internal
 */
class AppCheckTokenVerifier
{
    private const APP_CHECK_ISSUER_PREFIX = 'https://firebaseappcheck.googleapis.com/';

    private string $projectId;
    private CachedKeySet $keySet;

    public function __construct(string $projectId, CachedKeySet $keySet)
    {
        $this->projectId = $projectId;
        $this->keySet = $keySet;
    }

    /**
     * Verfies the format and signature of a Firebase App Check token.
     * 
     * @param string $token The Firebase Auth JWT token to verify.
     * 
     * @throws InvalidAppCheckToken If the token is invalid.
     * @throws FailedToVerifyAppCheckToken If the token could not be verified.
     * 
     * @return DecodedAppCheckToken 
     */
    public function verifyToken(string $token): DecodedAppCheckToken 
    {
        $decodedToken = $this->decodeJwt($token);

        $this->verifyContent($decodedToken);

        return $decodedToken;
    }

    /**
     * @param string $token The Firebase App Check JWT token to decode.
     * 
     * @throws InvalidAppCheckToken If the token is invalid.
     * @throws FailedToVerifyAppCheckToken If the token could not be verified.
     * 
     * @return DecodedAppCheckToken 
     */
    private function decodeJwt(string $token): DecodedAppCheckToken
    {
        try {
            $payload = (array) JWT::decode($token, $this->keySet);
        } catch (LogicException $e) {
            throw new InvalidAppCheckToken($e->getMessage(), $e->getCode(), $e);
        } catch (UnexpectedValueException $e) {
            throw new FailedToVerifyAppCheckToken($e->getMessage(), $e->getCode(), $e);
        }

        return DecodedAppCheckToken::fromArray($payload);
    }

    /**
     * Verifies the content of a Firebase App Check JWT.
     * 
     * @param DecodedAppCheckToken $token The decoded Firebase App Check token to verify.
     * 
     * @throws FailedToVerifyAppCheckToken If the token could not be verified.
     */
    private function verifyContent(DecodedAppCheckToken $token): void
    {
        if (empty($token->aud()) || ! in_array($this->projectId, $token->aud())) {
            throw new FailedToVerifyAppCheckToken('The "aud" claim must be set to the project ID.');
        }

        if (! is_string($token->iss()) || ! str_starts_with($token->iss(), self::APP_CHECK_ISSUER_PREFIX)) {
            throw new FailedToVerifyAppCheckToken('The provided App Check token has incorrect "iss" (issuer) claim.');
        }

        if (! is_string($token->sub())) {
            throw new FailedToVerifyAppCheckToken('The provided App Check token has no "sub" (subject) claim.');
        }
    }
}
