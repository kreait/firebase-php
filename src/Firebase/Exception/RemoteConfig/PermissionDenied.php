<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\RemoteConfig;

use Kreait\Firebase\Exception\RemoteConfigException;
use Throwable;

class PermissionDenied extends RemoteConfigException
{
    const IDENTIFER = 'PERMISSION_DENIED';

    public function __construct($code = 0, Throwable $previous = null)
    {
        parent::__construct('Permission denied', $code, $previous);
    }
}
