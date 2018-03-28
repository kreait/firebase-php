<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\RemoteConfig;

use Kreait\Firebase\Exception\RemoteConfigException;
use Throwable;

class OperationAborted extends RemoteConfigException
{
    const IDENTIFER = 'ABORTED';

    public function __construct($code = 0, Throwable $previous = null)
    {
        $message = 'Operation aborted. The reason is most probably that the remote config template has been updated remotely since you last fetched it.';

        parent::__construct($message, $code, $previous);
    }
}
