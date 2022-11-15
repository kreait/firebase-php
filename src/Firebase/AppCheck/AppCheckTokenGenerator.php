<?php

declare(strict_types=1);

namespace Kreait\Firebase\AppCheck;

use Beste\Clock\SystemClock;
use Firebase\JWT\JWT;
use Kreait\Firebase\Exception\AppCheck\InvalidAppCheckTokenOptions;
use Kreait\Firebase\ServiceAccount;
use StellaMaris\Clock\ClockInterface;

/**
 * @internal
 */
class AppCheckTokenGenerator
{
    private const APP_CHECK_AUDIENCE = 'https://firebaseappcheck.googleapis.com/google.firebase.appcheck.v1.TokenExchangeService';

    public function __construct(
        private ServiceAccount $serviceAccount,
        private ?ClockInterface $clock = null,
    ) {
        if (null == $this->clock) {
            $this->clock = new SystemClock();
        }
    }

    /**
     * @param non-empty-string $appId the Application Id to use for the generated token
     *
     * @throws InvalidAppCheckTokenOptions
     *
     * @return string the generated token
     */
    public function createCustomToken(string $appId, ?AppCheckTokenOptions $options = null): string
    {
        $now = $this->clock->now()->getTimestamp();
        $payload = [
            'iss' => $this->serviceAccount->getClientEmail(),
            'sub' => $this->serviceAccount->getClientEmail(),
            'app_id' => $appId,
            'aud' => self::APP_CHECK_AUDIENCE,
            'iat' => $now,
            'exp' => $now + 300,
        ];

        if (null !== $options && $options->ttl()) {
            $payload['ttl'] = $options->ttl().'s';
        }

        return JWT::encode($payload, $this->serviceAccount->getPrivateKey(), 'RS256');
    }
}
