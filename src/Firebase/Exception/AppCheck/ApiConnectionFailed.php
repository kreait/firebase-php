<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\AppCheck;

use Kreait\Firebase\Exception\AppCheckException;
use Kreait\Firebase\Exception\HasErrors;
use RuntimeException;

final class ApiConnectionFailed extends RuntimeException implements AppCheckException
{
    use HasErrors;
}
