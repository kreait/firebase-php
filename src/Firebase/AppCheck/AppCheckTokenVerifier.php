<?php

declare(strict_types=1);

namespace Kreait\Firebase\AppCheck;

use Firebase\JWT\CachedKeySet;
use Firebase\JWT\JWT;
use Kreait\Firebase\Exception\AppCheck\FailedToVerifyAppCheckToken;
use Kreait\Firebase\Exception\AppCheck\InvalidAppCheckToken;
use LogicException;
use Throwable;

use function in_array;
use function str_starts_with;

/**
 * @internal
 *
 * @phpstan-import-type DecodedAppCheckTokenShape from DecodedAppCheckToken
 */
final class AppCheckTokenVerifier
{
    private const APP_CHECK_ISSUER_PREFIX = 'https://firebaseappcheck.googleapis.com/';

    /**
     * @param non-empty-string $projectId
     */
    public function __construct(
        private readonly string $projectId,
        private readonly CachedKeySet $keySet,
    ) {
    }

    /**
     * Verifies the format and signature of a Firebase App Check token.
     *
     * @param string $token the Firebase Auth JWT token to verify
     *
     * @throws FailedToVerifyAppCheckToken if the token could not be verified
     * @throws InvalidAppCheckToken if the token is invalid
     */
    public function verifyToken(string $token): DecodedAppCheckToken
    {
        $decodedToken = $this->decodeJwt($token);

        $this->verifyContent($decodedToken);

        return $decodedToken;
    }

    /**
     * @param string $token the Firebase App Check JWT token to decode
     *
     * @throws FailedToVerifyAppCheckToken if the token could not be verified
     * @throws InvalidAppCheckToken if the token is invalid
     */
    private function decodeJwt(string $token): DecodedAppCheckToken
    {
        try {
            /** @var DecodedAppCheckTokenShape $payload */
            $payload = (array) JWT::decode($token, $this->keySet);
        } catch (LogicException $e) {
            throw new InvalidAppCheckToken($e->getMessage(), $e->getCode(), $e);
        } catch (Throwable $e) {
            throw new FailedToVerifyAppCheckToken($e->getMessage(), $e->getCode(), $e);
        }

        return DecodedAppCheckToken::fromArray($payload);
    }

    /**
     * Verifies the content of a Firebase App Check JWT.
     *
     * @param DecodedAppCheckToken $token the decoded Firebase App Check token to verify
     *
     * @throws FailedToVerifyAppCheckToken if the token could not be verified
     */
    private function verifyContent(DecodedAppCheckToken $token): void
    {
        if (!in_array('projects/'.$this->projectId, $token->aud, true)) {
            throw new FailedToVerifyAppCheckToken('The "aud" claim must include the project ID.');
        }

        if (!str_starts_with($token->iss, self::APP_CHECK_ISSUER_PREFIX)) {
            throw new FailedToVerifyAppCheckToken('The provided App Check token has incorrect "iss" (issuer) claim.');
        }
    }
}
