<?php

declare(strict_types=1);

namespace Kreait\Firebase\Contract;

use Google\Cloud\Firestore\FirestoreClient;

interface Firestore
{
    public function database(): FirestoreClient;
}
