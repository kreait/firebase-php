<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\Database;

use Kreait\Firebase\Exception\ApiException;
use Kreait\Firebase\Exception\DatabaseException;
use Kreait\Firebase\Exception\HasRequestAndResponse;
use Kreait\Firebase\Exception\PermissionDenied as DeprecatedPermissionDenied;
use RuntimeException;

final class PermissionDenied extends RuntimeException implements ApiException, DatabaseException, DeprecatedPermissionDenied
{
    use HasRequestAndResponse;
}
