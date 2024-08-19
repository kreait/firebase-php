<?php

declare(strict_types=1);

namespace Kreait\Firebase\Contract;

use Kreait\Firebase\AppCheck\AppCheckToken;
use Kreait\Firebase\AppCheck\AppCheckTokenOptions;
use Kreait\Firebase\AppCheck\VerifyAppCheckTokenResponse;
use Kreait\Firebase\Exception;
use Kreait\Firebase\Exception\AppCheck\FailedToVerifyAppCheckToken;
use Kreait\Firebase\Exception\AppCheck\InvalidAppCheckToken;
use Kreait\Firebase\Exception\AppCheck\InvalidAppCheckTokenOptions;

/**
 * @phpstan-import-type AppCheckTokenOptionsShape from AppCheckTokenOptions
 */
interface AppCheck
{
    /**
     * @param non-empty-string $appId
     * @param AppCheckTokenOptions|AppCheckTokenOptionsShape|null $options
     *
     * @throws InvalidAppCheckTokenOptions
     * @throws Exception\AppCheckException
     * @throws Exception\FirebaseException
     */
    public function createToken(string $appId, $options = null): AppCheckToken;

    /**
     * @param non-empty-string $appCheckToken
     *
     * @throws InvalidAppCheckToken
     * @throws FailedToVerifyAppCheckToken
     * @throws Exception\AppCheckException
     * @throws Exception\FirebaseException
     */
    public function verifyToken(string $appCheckToken): VerifyAppCheckTokenResponse;
}
