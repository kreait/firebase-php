<?php

declare(strict_types=1);

namespace Kreait\Firebase\Contract;

use Kreait\Firebase\AppCheck\AppCheckToken;
use Kreait\Firebase\AppCheck\AppCheckTokenOptions;
use Kreait\Firebase\AppCheck\VerifyAppCheckTokenResponse;
use Kreait\Firebase\Exception;

/**
 * @phpstan-import-type AppCheckTokenOptionsShape from AppCheckTokenOptions
 */
interface AppCheck
{
    /**
     * @param non-empty-string $appId
     * @param AppCheckTokenOptions|AppCheckTokenOptionsShape|null $options
     *
     * @throws Exception\AppCheck\InvalidAppCheckTokenOptions
     * @throws Exception\AppCheckException
     * @throws Exception\FirebaseException
     */
    public function createToken(string $appId, $options = null): AppCheckToken;

    /**
     * @param non-empty-string $appCheckToken
     *
     * @throws Exception\AppCheck\InvalidAppCheckToken
     * @throws Exception\AppCheck\FailedToVerifyAppCheckToken
     * @throws Exception\AppCheckException
     * @throws Exception\FirebaseException
     */
    public function verifyToken(string $appCheckToken): VerifyAppCheckTokenResponse;
}
