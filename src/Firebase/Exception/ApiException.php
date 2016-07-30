<?php

namespace Firebase\Exception;

class ApiException extends \RuntimeException implements FirebaseException
{
    private $debugMessage = '';

    public function setDebugMessage(string $message)
    {
        $this->debugMessage = $message;
    }

    public function hasDebugMessage(): bool
    {
        return (bool) $this->debugMessage;
    }

    public function getDebugMessage(): string
    {
        return $this->debugMessage;
    }
}
