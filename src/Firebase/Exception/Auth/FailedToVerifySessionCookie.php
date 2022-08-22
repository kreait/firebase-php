<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\Auth;

use Kreait\Firebase\Exception\FirebaseException;
use RuntimeException;

final class FailedToVerifySessionCookie extends RuntimeException implements FirebaseException
{
}
