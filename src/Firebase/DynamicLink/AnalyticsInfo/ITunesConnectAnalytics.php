<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink\AnalyticsInfo;

use JsonSerializable;

/**
 * @see https://www.macstories.net/tutorials/a-comprehensive-guide-to-the-itunes-affiliate-program/
 * @see https://blog.geni.us/parameter-cheat-sheet-for-itunes-and-app-store-links/
 *
 * @phpstan-type ITunesConnectAnalyticsShape array{
 *     at?: non-empty-string,
 *     ct?: non-empty-string,
 *     mt?: non-empty-string,
 *     pt?: non-empty-string
 * }
 */
final class ITunesConnectAnalytics implements JsonSerializable
{
    /**
     * @param ITunesConnectAnalyticsShape $data
     */
    private function __construct(private readonly array $data)
    {
    }

    /**
     * @param ITunesConnectAnalyticsShape $data
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
     * The iTunes connect/affiliate partner token.
     *
     * @see https://blog.geni.us/parameter-cheat-sheet-for-itunes-and-app-store-links/
     *
     * @param non-empty-string $affiliateToken
     */
    public function withAffiliateToken(string $affiliateToken): self
    {
        $data = $this->data;
        $data['at'] = $affiliateToken;

        return new self($data);
    }

    /**
     * The iTunes connect/affiliate partner token.
     *
     * @see https://blog.geni.us/parameter-cheat-sheet-for-itunes-and-app-store-links/
     *
     * @param non-empty-string $campaignToken
     */
    public function withCampaignToken(string $campaignToken): self
    {
        $data = $this->data;
        $data['ct'] = $campaignToken;

        return new self($data);
    }

    /**
     * The media type.
     *
     * @see https://blog.geni.us/parameter-cheat-sheet-for-itunes-and-app-store-links/
     *
     * @param non-empty-string $mediaType
     */
    public function withMediaType(string $mediaType): self
    {
        $data = $this->data;
        $data['mt'] = $mediaType;

        return new self($data);
    }

    /**
     * The provider token.
     *
     * @see https://www.macstories.net/tutorials/a-comprehensive-guide-to-the-itunes-affiliate-program/
     *
     * @param non-empty-string $providerToken
     */
    public function withProviderToken(string $providerToken): self
    {
        $data = $this->data;
        $data['pt'] = $providerToken;

        return new self($data);
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
