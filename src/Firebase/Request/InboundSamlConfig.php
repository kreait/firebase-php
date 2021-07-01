<?php

declare(strict_types=1);

namespace Kreait\Firebase\Request;

use Kreait\Firebase\IdentityPlatform\InboundSamlConfig as BaseConfig;

final class InboundSamlConfig extends BaseConfig implements \JsonSerializable
{
    /**
     * @return array<String,String>
     */
    public function jsonSerialize(): array
    {
        return \array_filter($this->toArray(), static fn ($value) => $value !== null);
    }
}
