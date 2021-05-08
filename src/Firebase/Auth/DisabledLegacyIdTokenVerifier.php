<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use Firebase\Auth\Token\Domain\Verifier;
use Kreait\Firebase\Exception\RuntimeException;
use Lcobucci\JWT\Token;

final class DisabledLegacyIdTokenVerifier implements Verifier
{
    private string $reason;

    public function __construct(string $reason)
    {
        $this->reason = $reason;
    }

    public function verifyIdToken($token): Token
    {
        throw new RuntimeException($this->reason);
    }
}
