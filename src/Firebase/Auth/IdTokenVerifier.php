<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use DateTimeImmutable;
use Firebase\Auth\Token\Domain\Verifier;
use Firebase\Auth\Token\Exception\ExpiredToken;
use Firebase\Auth\Token\Exception\InvalidSignature;
use Firebase\Auth\Token\Exception\InvalidToken;
use Firebase\Auth\Token\Exception\IssuedInTheFuture;
use Firebase\Auth\Token\Exception\UnknownKey;
use Kreait\Clock;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Token;
use Throwable;

final class IdTokenVerifier implements Verifier
{
    /** @var Verifier */
    private $verifier;

    /** @var Clock */
    private $clock;

    /** @var int */
    private $leewayInSeconds = 0;

    public function __construct(Verifier $verifier, Clock $clock)
    {
        $this->verifier = $verifier;
        $this->clock = $clock;
    }

    public function withLeewayInSeconds(int $leewayInSeconds): self
    {
        $verifier = new self($this->verifier, $this->clock);
        $verifier->leewayInSeconds = $leewayInSeconds;

        return $verifier;
    }

    public function verifyIdToken($token): Token
    {
        // We get $now now™ so that it doesn't change while processing
        $now = $this->clock->now();

        $token = $this->ensureToken($token);

        try {
            $this->verifier->verifyIdToken($token);

            // We're using getClaim() instead of hasClaim() to also check for an empty value
            if (!($token->getClaim('sub', false))) {
                throw new InvalidToken($token, 'The token has no "sub" claim');
            }

            return $token;
        } catch (UnknownKey $e) {
            throw $e;
        } catch (InvalidSignature $e) {
            throw $e;
        } catch (ExpiredToken $e) {
            // Re-check expiry with the clock
            if ($this->isNotExpired($token, $now)) {
                return $token;
            }

            throw $e;
        } catch (IssuedInTheFuture $e) {
            // Re-check expiry with the clock
            if ($this->isIssuedInThePast($token, $now)) {
                return $token;
            }

            throw $e;
        } catch (InvalidToken $e) {
            $isAuthTimeProblem = \mb_stripos($e->getMessage(), 'authentication time') !== false;
            if ($isAuthTimeProblem && $this->isAuthenticatedInThePast($token, $now)) {
                return $token;
            }

            throw $e;
        } catch (Throwable $e) {
            throw new InvalidToken($token, $e->getMessage(), $e->getCode(), $e);
        }
    }

    private function isNotExpired(Token $token, DateTimeImmutable $now): bool
    {
        $claim = $token->getClaim('exp');

        // We add another second to account for possible microseconds that could be in $now, but not in $expiresAt
        $check = $now->modify('-'.($this->leewayInSeconds + 1).' seconds');
        $expiresAt = $now->setTimestamp((int) $claim);

        return $expiresAt > $check;
    }

    private function isIssuedInThePast(Token $token, DateTimeImmutable $now): bool
    {
        $claim = $token->getClaim('iat');

        // We add another second to account for possible microseconds that could be in $now, but not in $issuedAt
        $check = $now->modify('+'.($this->leewayInSeconds + 1).' seconds');
        $issuedAt = $now->setTimestamp((int) $claim);

        return $issuedAt < $check;
    }

    private function isAuthenticatedInThePast(Token $token, DateTimeImmutable $now): bool
    {
        $claim = $token->getClaim('auth_time');

        // We add another second to account for possible microseconds that could be in $now, but not in $authenticatedAt
        $check = $now->modify('+'.($this->leewayInSeconds + 1).' seconds');
        $authenticatedAt = $now->setTimestamp((int) $claim);

        return $authenticatedAt < $check;
    }

    /**
     * @param Token|object|string $token
     */
    private function ensureToken($token): Token
    {
        if ($token instanceof Token) {
            return $token;
        }

        if (\is_object($token) && !\method_exists($token, '__toString')) {
            throw new InvalidArgumentException('The given token is an object and cannot be cast to a string');
        }

        try {
            return (new Parser())->parse((string) $token);
        } catch (Throwable $e) {
            throw new InvalidArgumentException('The given token could not be parsed: '.$e->getMessage());
        }
    }
}
