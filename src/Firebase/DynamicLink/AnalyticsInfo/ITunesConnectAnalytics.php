<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink\AnalyticsInfo;

use JsonSerializable;

/**
 * @see https://www.macstories.net/tutorials/a-comprehensive-guide-to-the-itunes-affiliate-program/
 * @see https://blog.geni.us/parameter-cheat-sheet-for-itunes-and-app-store-links/
 * @see https://affiliate.itunes.apple.com/resources/documentation/basic_affiliate_link_guidelines_for_the_phg_network/
 */
final class ITunesConnectAnalytics implements JsonSerializable
{
    /** @var array */
    private $data = [];

    private function __construct()
    {
    }

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
     * The iTunes connect/affiliate partner token.
     *
     * @see https://blog.geni.us/parameter-cheat-sheet-for-itunes-and-app-store-links/
     */
    public function withAffiliateToken(string $affiliateToken): self
    {
        $info = clone $this;
        $info->data['at'] = $affiliateToken;

        return $info;
    }

    /**
     * The iTunes connect/affiliate partner token.
     *
     * @see https://blog.geni.us/parameter-cheat-sheet-for-itunes-and-app-store-links/
     */
    public function withCampaignToken(string $campaignToken): self
    {
        $info = clone $this;
        $info->data['ct'] = $campaignToken;

        return $info;
    }

    /**
     * The media type.
     *
     * @see https://blog.geni.us/parameter-cheat-sheet-for-itunes-and-app-store-links/
     */
    public function withMediaType(string $mediaType): self
    {
        $info = clone $this;
        $info->data['mt'] = $mediaType;

        return $info;
    }

    /**
     * The provider token.
     *
     * @see https://www.macstories.net/tutorials/a-comprehensive-guide-to-the-itunes-affiliate-program/
     */
    public function withProviderToken(string $providerToken): self
    {
        $info = clone $this;
        $info->data['pt'] = $providerToken;

        return $info;
    }

    public function jsonSerialize()
    {
        return $this->data;
    }
}
