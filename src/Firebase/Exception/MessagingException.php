<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception;

interface MessagingException extends FirebaseException
{
    /**
     * @return array<mixed>
     */
    public function errors(): array;
}
