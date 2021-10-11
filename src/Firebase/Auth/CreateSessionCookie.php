<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use DateInterval;
use Kreait\Clock;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Lcobucci\JWT\Token;

final class CreateSessionCookie
{
    private const FIVE_MINUTES = 'PT5M';
    private const TWO_WEEKS = 'P14D';

    private string $idToken;
    private DateInterval $ttl;
    private Clock $clock;

    private function __construct(string $idToken, DateInterval $ttl, Clock $clock)
    {
        $this->idToken = $idToken;
        $this->ttl = $ttl;
        $this->clock = $clock;
    }

    /**
     * @param Token|string $idToken
     * @param int|DateInterval $ttl
     */
    public static function forIdToken($idToken, $ttl, ?Clock $clock = null): self
    {
        $clock ??= new Clock\SystemClock();

        if ($idToken instanceof Token) {
            $idToken = $idToken->toString();
        }

        $ttl = self::assertValidDuration($ttl, $clock);

        return new self($idToken, $ttl, $clock);
    }

    public function idToken(): string
    {
        return $this->idToken;
    }

    public function ttl(): DateInterval
    {
        return $this->ttl;
    }

    public function ttlInSeconds(): int
    {
        $now = $this->clock->now();

        return $now->add($this->ttl)->getTimestamp() - $now->getTimestamp();
    }

    /**
     * @param int|DateInterval $ttl
     *
     * @throws InvalidArgumentException
     */
    private static function assertValidDuration($ttl, Clock $clock): DateInterval
    {
        if (\is_int($ttl)) {
            if ($ttl < 0) {
                throw new InvalidArgumentException('A session cookie cannot be valid for a negative amount of time');
            }

            $ttl = new DateInterval('PT'.$ttl.'S');
        }

        $now = $clock->now();

        $expiresAt = $now->add($ttl);

        $min = $now->add(new DateInterval(self::FIVE_MINUTES));
        $max = $now->add(new DateInterval(self::TWO_WEEKS));

        if ($expiresAt >= $min && $expiresAt <= $max) {
            return $ttl;
        }

        throw new InvalidArgumentException('The TTL of a session must be between 5 minutes and 14 days');
    }
}
