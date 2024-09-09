<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\Auth;

use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\RuntimeException;
use Kreait\Firebase\Factory;

final class AuthError extends RuntimeException implements AuthException
{
    public static function missingProjectId(string $message): self
    {
        $factoryClass = Factory::class;

        $fullMessage = <<<MSG
            {$message}

            The current Firebase project is configured without a project ID. The project
            ID can be determined automatically with service account credentials, by
            providing a `GOOGLE_CLOUD_PROJECT=project_id` environment variable, or
            manually by using the respective method when instantiating the SDK's
            factory ({$factoryClass}).

            MSG;

        return new self($fullMessage);
    }
}
