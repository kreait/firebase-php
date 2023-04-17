<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\RemoteConfig;

use Kreait\Firebase\Exception\RemoteConfigException;
use Kreait\Firebase\Exception\RuntimeException;

final class ValidationFailed extends RuntimeException implements RemoteConfigException
{
}
