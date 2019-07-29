<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception;

use Psr\Http\Message\ResponseInterface;

interface MessagingException extends FirebaseException
{
    /**
     * @return string[]
     */
    public function errors(): array;

    /**
     * @deprecated 4.28.0
     *
     * @return ResponseInterface|null
     */
    public function response();
}
