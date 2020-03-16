<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\Messaging;

use InvalidArgumentException;
use Kreait\Firebase\Exception\HasErrors;
use Kreait\Firebase\Exception\MessagingException;

final class InvalidArgument extends InvalidArgumentException implements MessagingException
{
    use HasErrors;
}
