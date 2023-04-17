<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\RemoteConfig;

use Kreait\Firebase\Exception\RemoteConfigException;
use Kreait\Firebase\Exception\RuntimeException;
use Kreait\Firebase\RemoteConfig\VersionNumber;

final class VersionNotFound extends RuntimeException implements RemoteConfigException
{
    public static function withVersionNumber(VersionNumber $versionNumber): self
    {
        return new self('Version #'.$versionNumber.' could not be found.');
    }
}
