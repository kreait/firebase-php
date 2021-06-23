<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\IdentityPlatform;

use Kreait\Firebase\Exception\IdentityPlatformException;
use RuntimeException;

final class ApiConnectionFailed extends RuntimeException implements IdentityPlatformException
{
}
