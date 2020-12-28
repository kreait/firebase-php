<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

final class DeleteUsersResult
{
    /** @var int */
    private $users;

    /** @var array<DeleteUserError> */
    private $errors;

    /**
     * @param array<DeleteUserError> $errors
     */
    public function __construct(int $users, array $errors = [])
    {
        $this->users = $users;
        $this->errors = $errors;
    }

    public function getSuccessCount(): int
    {
        return $this->users - \count($this->errors);
    }

    public function getFailureCount(): int
    {
        return \count($this->errors);
    }

    /**
     * @return array<DeleteUserError>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
