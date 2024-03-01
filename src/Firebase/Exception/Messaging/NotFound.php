<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\Messaging;

use Kreait\Firebase\Exception\HasErrors;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Exception\RuntimeException;

final class NotFound extends RuntimeException implements MessagingException
{
    use HasErrors;

    /**
     * @var non-empty-string|null
     */
    private ?string $token = null;

    /**
     * @param non-empty-string $token
     * @param array<mixed> $errors
     */
    public static function becauseTokenNotFound(string $token, array $errors = []): self
    {
        $message = <<<MESSAGE


            The message could not be delivered to the device identified by '{$token}'.

            Although the token is syntactically correct, it is not known to the Firebase
            project you are using. This could have the following reasons:

            - The token has been unregistered from the project. This can happen when a user
              has logged out from the application on the given client, or if they have
              uninstalled or re-installed the application.

            - The token has been registered to a different Firebase project than the project
              you are using to send the message. A common reason for this is when you work
              with different application environments and are sending a message from one
              environment to a device in another environment.


            MESSAGE;

        $notFound = new self($message);
        $notFound->errors = $errors;
        $notFound->token = $token;

        return $notFound;
    }

    /**
     * @internal
     *
     * @param array<mixed> $errors
     */
    public function withErrors(array $errors): self
    {
        $new = new self($this->getMessage(), $this->getCode(), $this->getPrevious());
        $new->errors = $errors;

        return $new;
    }

    public function token(): ?string
    {
        return $this->token;
    }
}
