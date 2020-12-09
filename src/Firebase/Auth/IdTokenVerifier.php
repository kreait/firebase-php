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
use Lcobucci\JWT\Configuration;
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

    /** @var Configuration */
    private $config;

    public function __construct(Verifier $verifier, Clock $clock)
    {
        $this->verifier = $verifier;
        $this->clock = $clock;
        $this->config = Configuration::forUnsecuredSigner();
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

        // @codeCoverageIgnoreStart
        if (!($token instanceof Token\Plain)) {
            throw new InvalidArgumentException('The given token could not be decrypted');
        }
        // @codeCoverageIgnoreEnd

        try {
            $verifiedToken = $this->verifier->verifyIdToken($token);

            // @codeCoverageIgnoreStart
            if (!($verifiedToken instanceof Token\Plain)) {
                throw new InvalidToken($token, 'The token could not be decrypted');
            }
            // @codeCoverageIgnoreEnd

            // We're using getClaim() instead of hasClaim() to also check for an empty value
            if (!($verifiedToken->claims()->get('sub', false))) {
                throw new InvalidToken($token, 'The token has no "sub" claim');
            }

            return $token;
        } catch (UnknownKey | InvalidSignature $e) {
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
        return $token->isExpired($now->modify('-'.($this->leewayInSeconds + 1).' seconds'));
    }

    private function isIssuedInThePast(Token $token, DateTimeImmutable $now): bool
    {
        return $token->hasBeenIssuedBefore($now->modify('+'.($this->leewayInSeconds + 1).' seconds'));
    }

    private function isAuthenticatedInThePast(Token $token, DateTimeImmutable $now): bool
    {
        if (!($token instanceof Token\Plain)) {
            return false;
        }

        $issuedAt = $token->claims()->get('auth_time');

        // We add another second to account for possible microseconds that could be in $now, but not in $authenticatedAt
        $check = $now->modify('+'.($this->leewayInSeconds + 1).' seconds');
        $authenticatedAt = $now->setTimestamp((int) $issuedAt);

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
            return $this->config->parser()->parse((string) $token);
        } catch (Throwable $e) {
            throw new InvalidArgumentException('The given token could not be parsed: '.$e->getMessage());
        }
    }
}
