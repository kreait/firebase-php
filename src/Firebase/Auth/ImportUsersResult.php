<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

class ImportUsersResult
{
    /** @var array<ImportUserError> */
    private $errors;

    /** @var int */
    private $users;

    /**
     * @param array<ImportUserError> $errors
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
     * @return array<ImportUserError>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
