<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
trait HasErrors
{
    /** @var array<mixed> */
    protected array $errors = [];

    /**
     * @return array<mixed>
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
