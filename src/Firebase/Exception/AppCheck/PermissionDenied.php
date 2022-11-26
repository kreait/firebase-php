<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\AppCheck;

use Kreait\Firebase\Exception\AppCheckException;
use RuntimeException;

final class PermissionDenied extends RuntimeException implements AppCheckException
{
}
