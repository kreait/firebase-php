<?php

declare(strict_types=1);

namespace Kreait\Firebase\Contract;

interface FirestoreProvider
{
    public function firestore(): Firestore;
}
