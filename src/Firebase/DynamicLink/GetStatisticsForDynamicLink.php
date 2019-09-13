<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink;

use Kreait\Firebase\DynamicLink;
use Kreait\Firebase\Value\Url;
use Psr\Http\Message\UriInterface;

final class GetStatisticsForDynamicLink
{
    const DEFAULT_DURATION_IN_DAYS = 7;

    /** @var string */
    private $dynamicLink;

    /** @var int */
    private $durationInDays;

    private function __construct()
    {
    }

    /**
     * @param Url|UriInterface|string|DynamicLink|mixed $link
     */
    public static function forLink($link): self
    {
        $action = new self();
        $action->dynamicLink = (string) Url::fromValue((string) $link);
        $action->durationInDays = self::DEFAULT_DURATION_IN_DAYS;

        return $action;
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
