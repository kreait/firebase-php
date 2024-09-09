<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\RemoteConfig;

use Kreait\Firebase\Exception\RemoteConfigException;
use Kreait\Firebase\Exception\RuntimeException;

final class ApiConnectionFailed extends RuntimeException implements RemoteConfigException
{
}
