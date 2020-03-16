<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\Auth;

use Kreait\Firebase\Exception\AuthException;
use RuntimeException;

final class EmailExists extends RuntimeException implements AuthException
{
}
