<?php

declare(strict_types=1);

namespace Kreait\Firebase\Contract;

use Kreait\Firebase\Exception\FirebaseException;

interface DatabaseProvider
{
    /**
     * @throws FirebaseException when the requested database could not be provided
     */
    public function database(?string $url = null): Database;
}
