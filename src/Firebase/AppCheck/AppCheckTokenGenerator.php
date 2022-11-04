<?php

declare(strict_types=1);

namespace Kreait\Firebase\AppCheck;

use Beste\Clock\SystemClock;
use Firebase\JWT\JWT;
use Kreait\Firebase\Exception\AppCheck\InvalidAppCheckTokenOptions;
use Kreait\Firebase\ServiceAccount;
use StellaMaris\Clock\ClockInterface;

use function is_null;
use function is_numeric;

/**
 * @internal
 */
class AppCheckTokenGenerator
{
    private const APP_CHECK_AUDIENCE = 'https://firebaseappcheck.googleapis.com/google.firebase.appcheck.v1.TokenExchangeService';

    private ServiceAccount $serviceAccount;
    private ClockInterface $clock;

    public function __construct(ServiceAccount $serviceAccount, ClockInterface $clock = null)
    {
        $this->serviceAccount = $serviceAccount;
        $this->clock = $clock ?? SystemClock::create();
    }

    /**
     * @param string $appId The Application Id to use for the generated token.
     * @param AppCheckTokenOptions|null $options 
     * 
     * @return string The generated token.
     */
    public function createCustomToken(string $appId, AppCheckTokenOptions $options = null) : string
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

        if (! is_null($options)) {
            $this->validateOptions($options);

            $payload = array_merge($payload, $options->toArray());
        }

        return JWT::encode($payload, $this->serviceAccount->getPrivateKey(), 'RS256');
    }

    private function validateOptions(AppCheckTokenOptions $options): void
    {
        if (! is_numeric($options->ttl())) {
            throw new InvalidAppCheckTokenOptions('The ttl must be a number.');
        }

        if ($options->ttl() < 1800 || $options->ttl() > 604800) {
            throw new InvalidAppCheckTokenOptions('The ttl must be a duration between 30 minutes and 7 days.');
        }
    }
}
