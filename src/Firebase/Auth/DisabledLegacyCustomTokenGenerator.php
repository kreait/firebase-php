<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use Firebase\Auth\Token\Domain\Generator;
use Kreait\Firebase\Exception\RuntimeException;
use Lcobucci\JWT\Token;

final class DisabledLegacyCustomTokenGenerator implements Generator
{
    private string $reason;

    public function __construct(string $reason)
    {
        $this->reason = $reason;
    }

    /**
     * @param \Stringable|string $uid
     * @param array<string, mixed> $claims
     */
    public function createCustomToken($uid, array $claims = []): Token
    {
        throw new RuntimeException($this->reason);
    }
}
