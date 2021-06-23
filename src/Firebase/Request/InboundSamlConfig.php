<?php

namespace Kreait\Firebase\Request;

use Kreait\Firebase\IdentityPlatform\InboundSamlConfig as IdentityPlatformInboundSamlConfig;

final class InboundSamlConfig extends IdentityPlatformInboundSamlConfig implements \JsonSerializable
{
    public function jsonSerialize()
    {
        return \array_filter($this->toArray(), static fn ($value) => $value !== null);
    }
}
