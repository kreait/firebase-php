<?php

declare(strict_types=1);

namespace Kreait\Firebase\AppCheck;

/**
 * @phpstan-import-type DecodedAppCheckTokenShape from DecodedAppCheckToken
 *
 * @phpstan-type VerifyAppCheckTokenResponseShape array{
 *     appId: non-empty-string,
 *     token: DecodedAppCheckTokenShape,
 * }
 */
final readonly class VerifyAppCheckTokenResponse
{
    /**
     * @param non-empty-string $appId
     */
    public function __construct(
        public string $appId,
        public DecodedAppCheckToken $token,
    ) {
    }
}
