<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception;

/**
 * @codeCoverageIgnore
 */
trait HasErrors
{
    /** @var string[] */
    protected $errors = [];

    /**
     * @return string[]
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
