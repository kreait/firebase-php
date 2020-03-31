<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink\AnalyticsInfo;

use JsonSerializable;

final class GooglePlayAnalytics implements JsonSerializable
{
    /** @var array<string, string> */
    private $data = [];

    private function __construct()
    {
    }

    /**
     * @param array<string, string> $data
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
     * Identifies the advertiser, site, publication, etc. that is sending traffic to your property,
     * for example: google, newsletter4, billboard.
     *
     * @see https://support.google.com/analytics/answer/1033863#parameters
     */
    public function withUtmSource(string $utmSource): self
    {
        $info = clone $this;
        $info->data['utmSource'] = $utmSource;

        return $info;
    }

    /**
     * The advertising or marketing medium, for example: cpc, banner, email newsletter.
     *
     * @see https://support.google.com/analytics/answer/1033863#parameters
     */
    public function withUtmMedium(string $utmMedium): self
    {
        $info = clone $this;
        $info->data['utmMedium'] = $utmMedium;

        return $info;
    }

    /**
     * The individual campaign name, slogan, promo code, etc. for a product.
     *
     * @see https://support.google.com/analytics/answer/1033863#parameters
     */
    public function withUtmCampaign(string $utmCampaing): self
    {
        $info = clone $this;
        $info->data['utmCampaign'] = $utmCampaing;

        return $info;
    }

    /**
     * Identifies paid search keywords. If you're manually tagging paid keyword campaigns, you should also use
     * utm_term to specify the keyword.
     *
     * @see https://support.google.com/analytics/answer/1033863#parameters
     */
    public function withUtmTerm(string $utmTerm): self
    {
        $info = clone $this;
        $info->data['utmTerm'] = $utmTerm;

        return $info;
    }

    /**
     * Used to differentiate similar content, or links within the same ad. For example, if you have two call-to-action
     * links within the same email message, you can use utm_content and set different values for each so you can tell
     * which version is more effective.
     *
     * @see https://support.google.com/analytics/answer/1033863#parameters
     */
    public function withUtmContent(string $utmContent): self
    {
        $info = clone $this;
        $info->data['utmContent'] = $utmContent;

        return $info;
    }

    /**
     * The Google Click ID.
     *
     * @see https://support.google.com/analytics/answer/2938246?hl=en
     */
    public function withGclid(string $gclid): self
    {
        $info = clone $this;
        $info->data['gclid'] = $gclid;

        return $info;
    }

    /**
     * @return array<string, string>
     */
    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
