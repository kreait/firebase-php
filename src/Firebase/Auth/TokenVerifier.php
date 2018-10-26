<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use Kreait\Firebase\Exception\InvalidToken;
use Kreait\Firebase\Exception\RuntimeException;
use Kreait\Firebase\Util\Duration;
use Lcobucci\JWT\Token;

interface TokenVerifier
{
    /**
     * Verifies a JWT auth token.
     *
     * @param Token|string $token the JWT auth token
     * @param Duration|mixed|null $leeway Allows to account for time differences between environments
     *
     * @throws InvalidToken when the given token is invalid
     * @throws RuntimeException when an error occured while verifying the token
     */
    public function verify($token, $leeway = null);
}
