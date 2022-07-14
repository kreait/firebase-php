<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink;

use Kreait\Firebase\Value\Url;

final class GetStatisticsForDynamicLink
{
    public const DEFAULT_DURATION_IN_DAYS = 7;

    private string $dynamicLink;
    private int $durationInDays = self::DEFAULT_DURATION_IN_DAYS;

    private function __construct(string $dynamicLink)
    {
        $this->dynamicLink = $dynamicLink;
    }

    /**
     * @param \Stringable|string $link
     */
    public static function forLink($link): self
    {
        return new self((string) Url::fromValue((string) $link));
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
