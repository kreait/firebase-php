<?php

declare(strict_types=1);

namespace Kreait\Firebase\AppCheck;

use JsonSerializable;

/**
 * @phpstan-import-type DecodedAppCheckTokenShape from DecodedAppCheckToken
 *
 * @phpstan-type VerifyAppCheckTokenResponseShape array{
 *     appId: non-empty-string,
 *     token: DecodedAppCheckTokenShape,
 * }
 */
final class VerifyAppCheckTokenResponse implements JsonSerializable
{
    /**
     * @param non-empty-string $appId
     */
    public function __construct(
        public readonly string $appId,
        public readonly DecodedAppCheckToken $token,
    ) {
    }

    /**
     * @return VerifyAppCheckTokenResponseShape
     */
    public function toArray(): array
    {
        return [
            'appId' => $this->appId,
            'token' => $this->token->toArray(),
        ];
    }

    /**
     * @return VerifyAppCheckTokenResponseShape
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
