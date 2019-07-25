<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception;

use Throwable;

interface ExceptionConverter
{
    public function convertException(Throwable $exception): FirebaseException;
}
