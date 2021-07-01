<?php

declare(strict_types=1);

namespace Kreait\Firebase\Request;

use JsonSerializable;
use Kreait\Firebase\IdentityPlatform\OAuthIdpConfig as IdentityPlatformOauthIdpConfig;

class OAuthIdpConfig extends IdentityPlatformOauthIdpConfig implements JsonSerializable
{
    /**
     * @return array<String,String>
     */
    public function jsonSerialize(): array
    {
        return \array_filter($this->toArray(), static fn ($value) => $value !== null);
    }
}
