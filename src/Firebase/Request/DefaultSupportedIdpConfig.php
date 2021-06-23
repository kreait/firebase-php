<?php

declare(strict_types=1);

namespace Kreait\Firebase\Request;

use Kreait\Firebase\Request;
use Kreait\Firebase\IdentityPlatform\DefaultSupportedIdpConfig as BaseConfig;

final class DefaultSupportedIdpConfig extends BaseConfig implements Request
{
    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return \array_filter($this->toArray(), static fn ($value) => $value !== null);
    }
}
