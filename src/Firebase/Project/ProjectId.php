<?php

declare(strict_types=1);

namespace Kreait\Firebase\Project;

final class ProjectId
{
    /** @var string */
    private $value = '';

    private function __construct()
    {
    }

    public static function fromString(string $value): self
    {
        $id = new self();
        $id->value = $value;

        return $id;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function sanitizedValue(): string
    {
        if ($sanitizedValue = \preg_replace('/[^A-Za-z0-9\-]/', '-', $this->value)) {
            return $sanitizedValue;
        }

        // @codeCoverageIgnoreStart
        // This should never happen, however:
        // If the regex above is invalid, preg_replace() will return false.
        // As it's not invalid, code coverage will complain about this
        // never being reached, so we ignore it ¯\_(ツ)_/¯
        return $this->value;
        // @codeCoverageIgnoreEnd
    }
}
