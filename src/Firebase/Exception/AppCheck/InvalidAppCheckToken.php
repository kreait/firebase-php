<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\AppCheck;

use Kreait\Firebase\Exception\AppCheckException;
use Kreait\Firebase\Exception\RuntimeException;

final class InvalidAppCheckToken extends RuntimeException implements AppCheckException
{
}
