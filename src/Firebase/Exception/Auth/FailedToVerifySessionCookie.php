<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\Auth;

use Kreait\Firebase\Exception\FirebaseException;

final class FailedToVerifySessionCookie extends \RuntimeException implements FirebaseException
{
}
