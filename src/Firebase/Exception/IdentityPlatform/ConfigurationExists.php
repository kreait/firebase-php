<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\IdentityPlatform;

use Kreait\Firebase\Exception\IdentityPlatformException;
use RuntimeException;

class ConfigurationExists extends RuntimeException implements IdentityPlatformException
{
}
