<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\Messaging;

use Kreait\Firebase\Exception\HasErrors;
use Kreait\Firebase\Exception\HasRequestAndResponse;
use Kreait\Firebase\Exception\MessagingException;
use RuntimeException;

final class ApiConnectionFailed extends RuntimeException implements MessagingException
{
    use HasRequestAndResponse;
    use HasErrors;
}
