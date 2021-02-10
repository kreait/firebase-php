<?php

declare(strict_types=1);

namespace Kreait\Firebase\Contract;

use Kreait\Firebase\Firestore\ApiClient;

interface Firestore
{
    public function database(): ApiClient;
}
