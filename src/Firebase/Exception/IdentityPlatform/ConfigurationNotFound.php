<?php
namespace Kreait\Firebase\Exception\IdentityPlatform;

use Kreait\Firebase\Exception\IdentityPlatformException;
use RuntimeException;

class ConfigurationNotFound extends RuntimeException implements IdentityPlatformException
{
}
