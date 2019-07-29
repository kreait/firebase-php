<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\RemoteConfig;

use Kreait\Firebase\Exception\RemoteConfigException;
use RuntimeException;

final class VersionMismatch extends RuntimeException implements RemoteConfigException
{
}
