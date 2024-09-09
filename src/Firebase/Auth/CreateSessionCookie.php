<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use Beste\Clock\SystemClock;
use Beste\Clock\WrappingClock;
use DateInterval;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Lcobucci\JWT\Token;
use Psr\Clock\ClockInterface;

use function is_int;

/**
 * @internal
 */
final class CreateSessionCookie
{
    private const FIVE_MINUTES = 'PT5M';
    private const TWO_WEEKS = 'P14D';

    private function __construct(
        private readonly string $idToken,
        private readonly ?string $tenantId,
        private readonly DateInterval $ttl,
        private readonly ClockInterface $clock,
    ) {
    }

    /**
     * @param Token|string $idToken
     * @param int|DateInterval $ttl
     */
    public static function forIdToken($idToken, ?string $tenantId, $ttl, ?object $clock = null): self
    {
        $clock ??= SystemClock::create();

        if (!$clock instanceof ClockInterface) {
            $clock = WrappingClock::wrapping($clock);
        }

        if ($idToken instanceof Token) {
            $idToken = $idToken->toString();
        }

        $ttl = self::assertValidDuration($ttl, $clock);

        return new self($idToken, $tenantId, $ttl, $clock);
    }

    public function idToken(): string
    {
        return $this->idToken;
    }

    public function tenantId(): ?string
    {
        return $this->tenantId;
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
    private static function assertValidDuration($ttl, ClockInterface $clock): DateInterval
    {
        if (is_int($ttl)) {
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
