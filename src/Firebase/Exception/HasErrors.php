<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception;

/**
 * @internal
 */
trait HasErrors
{
    /**
     * @var array<non-empty-string>
     */
    protected array $errors = [];

    /**
     * @return array<non-empty-string>
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
