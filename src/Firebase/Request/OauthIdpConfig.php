<?php

namespace Kreait\Firebase\Request;

use Kreait\Firebase\IdentityPlatform\OAuthIdpConfig as IdentityPlatformOauthIdpConfig;
use JsonSerializable;

class OAuthIdpConfig extends IdentityPlatformOauthIdpConfig implements JsonSerializable
{
    public function jsonSerialize() : array
    {
        return \array_filter($this->toArray(), static fn ($value) => $value !== null);
    }
}
