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
     * @param non-empty string $appId 
     */
    public function __construct(
        private string $appId,
        private DecodedAppCheckToken $token,
    ) {
        $this->appId = $appId;
        $this->token = $token;
    }

    public function appId(): string
    {
        return $this->appId;
    }

    public function token(): DecodedAppCheckToken
    {
        return $this->token;
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
