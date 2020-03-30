<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception;

interface MessagingException extends FirebaseException
{
    /**
     * @return string[]
     */
    public function errors(): array;
}
