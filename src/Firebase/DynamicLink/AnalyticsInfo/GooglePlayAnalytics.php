<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink\AnalyticsInfo;

use JsonSerializable;

/**
 * @phpstan-type GooglePlayAnalyticsShape array{
 *     utmSource?: non-empty-string,
 *     utmMedium?: non-empty-string,
 *     utmCampaign?: non-empty-string,
 *     utmTerm?: non-empty-string,
 *     utmContent?: non-empty-string,
 *     gclid?: non-empty-string
 * }
 */
final class GooglePlayAnalytics implements JsonSerializable
{
    /**
     * @param GooglePlayAnalyticsShape $data
     */
    private function __construct(private readonly array $data)
    {
    }

    /**
     * @param GooglePlayAnalyticsShape $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    public static function new(): self
    {
        return new self([]);
    }

    /**
     * Identifies the advertiser, site, publication, etc. that is sending traffic to your property,
     * for example: google, newsletter4, billboard.
     *
     * @see https://support.google.com/analytics/answer/1033863#parameters
     *
     * @param non-empty-string $utmSource
     */
    public function withUtmSource(string $utmSource): self
    {
        $data = $this->data;
        $data['utmSource'] = $utmSource;

        return new self($data);
    }

    /**
     * The advertising or marketing medium, for example: cpc, banner, email newsletter.
     *
     * @see https://support.google.com/analytics/answer/1033863#parameters
     *
     * @param non-empty-string $utmMedium
     */
    public function withUtmMedium(string $utmMedium): self
    {
        $data = $this->data;
        $data['utmMedium'] = $utmMedium;

        return new self($data);
    }

    /**
     * The individual campaign name, slogan, promo code, etc. for a product.
     *
     * @see https://support.google.com/analytics/answer/1033863#parameters
     *
     * @param non-empty-string $utmCampaign
     */
    public function withUtmCampaign(string $utmCampaign): self
    {
        $data = $this->data;
        $data['utmCampaign'] = $utmCampaign;

        return new self($data);
    }

    /**
     * Identifies paid search keywords. If you're manually tagging paid keyword campaigns, you should also use
     * utm_term to specify the keyword.
     *
     * @see https://support.google.com/analytics/answer/1033863#parameters
     *
     * @param non-empty-string $utmTerm
     */
    public function withUtmTerm(string $utmTerm): self
    {
        $data = $this->data;
        $data['utmTerm'] = $utmTerm;

        return new self($data);
    }

    /**
     * Used to differentiate similar content, or links within the same ad. For example, if you have two call-to-action
     * links within the same email message, you can use utm_content and set different values for each, so you can tell
     * which version is more effective.
     *
     * @see https://support.google.com/analytics/answer/1033863#parameters
     *
     * @param non-empty-string $utmContent
     */
    public function withUtmContent(string $utmContent): self
    {
        $data = $this->data;
        $data['utmContent'] = $utmContent;

        return new self($data);
    }

    /**
     * The Google Click ID.
     *
     * @see https://support.google.com/analytics/answer/2938246?hl=en
     *
     * @param non-empty-string $gclid
     */
    public function withGclid(string $gclid): self
    {
        $data = $this->data;
        $data['gclid'] = $gclid;

        return new self($data);
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
