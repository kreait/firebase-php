<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink;

use JsonSerializable;
use Kreait\Firebase\DynamicLink\AnalyticsInfo\GooglePlayAnalytics;
use Kreait\Firebase\DynamicLink\AnalyticsInfo\ITunesConnectAnalytics;

final class AnalyticsInfo implements JsonSerializable
{
    /** @var array<string, mixed> */
    private array $data = [];

    private function __construct()
    {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $info = new self();
        $info->data = $data;

        return $info;
    }

    public static function new(): self
    {
        return new self();
    }

    /**
     * @param GooglePlayAnalytics|array<string, string> $data
     */
    public function withGooglePlayAnalyticsInfo($data): self
    {
        $gpInfo = $data instanceof GooglePlayAnalytics ? $data : GooglePlayAnalytics::fromArray($data);

        $info = clone $this;
        $info->data['googlePlayAnalytics'] = $gpInfo;

        return $info;
    }

    /**
     * @param ITunesConnectAnalytics|array<string, string> $data
     */
    public function withItunesConnectAnalytics($data): self
    {
        $gpInfo = $data instanceof ITunesConnectAnalytics ? $data : ITunesConnectAnalytics::fromArray($data);

        $info = clone $this;
        $info->data['itunesConnectAnalytics'] = $gpInfo;

        return $info;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
