<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink;

use Kreait\Firebase\Value\Url;
use Stringable;

final class GetStatisticsForDynamicLink
{
    public const DEFAULT_DURATION_IN_DAYS = 7;
    private int $durationInDays = self::DEFAULT_DURATION_IN_DAYS;

    private function __construct(private readonly string $dynamicLink)
    {
    }

    public static function forLink(Stringable|string $link): self
    {
        return new self(Url::fromString($link)->value);
    }

    public function withDurationInDays(int $durationInDays): self
    {
        $action = clone $this;
        $action->durationInDays = $durationInDays;

        return $action;
    }

    public function dynamicLink(): string
    {
        return $this->dynamicLink;
    }

    public function durationInDays(): int
    {
        return $this->durationInDays;
    }
}
