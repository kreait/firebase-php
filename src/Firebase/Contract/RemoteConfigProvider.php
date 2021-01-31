<?php

declare(strict_types=1);

namespace Kreait\Firebase\Contract;

interface RemoteConfigProvider
{
    public function remoteConfig(): RemoteConfig;
}
