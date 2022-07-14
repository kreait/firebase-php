<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\Auth;

use Kreait\Firebase\Exception\FirebaseException;

final class FailedToVerifyToken extends \RuntimeException implements FirebaseException
{
}
