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
     * @param string $appId
     * @param AppCheckTokenOptions|AppCheckTokenOptionsShape|null $options
     *
     * @throws Exception\AppCheckException
     * @throws Exception\FirebaseException
     * 
     * @return AppCheckToken
     */
    public function createToken(string $appId, $options = null): AppCheckToken;

    /**
     * @param string $appCheckToken 
     *
     * @throws Exception\AppCheck\InvalidAppCheckToken
     * @throws Exception\AppCheck\FailedToVerifyAppCheckToken 
     * @throws Exception\AppCheckException
     * @throws Exception\FirebaseException
     * 
     * @return VerifyAppCheckTokenResponse 
     */
    public function verifyToken(string $appCheckToken) : VerifyAppCheckTokenResponse;
}
