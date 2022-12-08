<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink;

use Beste\Json;
use JsonSerializable;
use Kreait\Firebase\Value\Url;
use Stringable;

/**
 * @phpstan-import-type AnalyticsInfoShape from AnalyticsInfo
 * @phpstan-import-type AndroidInfoShape from AndroidInfo
 * @phpstan-import-type IOSInfoShape from IOSInfo
 * @phpstan-import-type NavigationInfoShape from NavigationInfo
 * @phpstan-import-type SocialMetaTagInfoShape from SocialMetaTagInfo
 *
 * @phpstan-type CreateDynamicLinkShape array{
 *     dynamicLinkInfo: array{
 *         link?: non-empty-string,
 *         domainUriPrefix?: non-empty-string,
 *         analyticsInfo?: AnalyticsInfoShape,
 *         androidInfo?: AndroidInfoShape,
 *         iosInfo?: IOSInfoShape,
 *         navigationInfo?: NavigationInfoShape,
 *         socialMetaTagInfo?: SocialMetaTagInfoShape
 *     },
 *     suffix: array{
 *         option: self::WITH_*
 *     }
 * }
 */
final class CreateDynamicLink implements JsonSerializable
{
    public const WITH_UNGUESSABLE_SUFFIX = 'UNGUESSABLE';
    public const WITH_SHORT_SUFFIX = 'SHORT';

    /**
     * @param CreateDynamicLinkShape $data
     */
    private function __construct(private readonly array $data)
    {
    }

    /**
     * @param CreateDynamicLinkShape $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    public static function new(): self
    {
        return new self([
            'dynamicLinkInfo' => [],
            'suffix' => ['option' => self::WITH_UNGUESSABLE_SUFFIX],
        ]);
    }

    /**
     * The link your app will open. Specify a URL that your app can handle, typically the app's content
     * or payload, which initiates app-specific logic (such as crediting the user with a coupon or
     * displaying a welcome screen). This link must be a well-formatted URL, be properly
     * URL-encoded, use either HTTP or HTTPS, and cannot be another Dynamic Link.
     */
    public static function forUrl(Stringable|string $url): self
    {
        return new self([
            'dynamicLinkInfo' => [
                'link' => Url::fromString($url)->value,
            ],
            'suffix' => ['option' => self::WITH_UNGUESSABLE_SUFFIX],
        ]);
    }

    public function withDynamicLinkDomain(Stringable|string $dynamicLinkDomain): self
    {
        $data = $this->data;
        $data['dynamicLinkInfo']['domainUriPrefix'] = Url::fromString($dynamicLinkDomain)->value;

        return new self($data);
    }

    public function hasDynamicLinkDomain(): bool
    {
        return (bool) ($this->data['dynamicLinkInfo']['domainUriPrefix'] ?? null);
    }

    /**
     * @param AnalyticsInfo|AnalyticsInfoShape $data
     */
    public function withAnalyticsInfo(AnalyticsInfo|array $data): self
    {
        $info = $data instanceof AnalyticsInfo ? $data : AnalyticsInfo::fromArray($data);

        $data = $this->data;
        $data['dynamicLinkInfo']['analyticsInfo'] = Json::decode(Json::encode($info), true);

        return new self($data);
    }

    /**
     * @param AndroidInfo|AndroidInfoShape $data
     */
    public function withAndroidInfo(AndroidInfo|array $data): self
    {
        $info = $data instanceof AndroidInfo ? $data : AndroidInfo::fromArray($data);

        $data = $this->data;
        $data['dynamicLinkInfo']['androidInfo'] = Json::decode(Json::encode($info), true);

        return new self($data);
    }

    /**
     * @param IOSInfo|IOSInfoShape $data
     */
    public function withIOSInfo(IOSInfo|array $data): self
    {
        $info = $data instanceof IOSInfo ? $data : IOSInfo::fromArray($data);

        $data = $this->data;
        $data['dynamicLinkInfo']['iosInfo'] = Json::decode(Json::encode($info), true);

        return new self($data);
    }

    /**
     * @param NavigationInfo|NavigationInfoShape $data
     */
    public function withNavigationInfo(NavigationInfo|array $data): self
    {
        $info = $data instanceof NavigationInfo ? $data : NavigationInfo::fromArray($data);

        $data = $this->data;
        $data['dynamicLinkInfo']['navigationInfo'] = Json::decode(Json::encode($info), true);

        return new self($data);
    }

    /**
     * @param SocialMetaTagInfo|SocialMetaTagInfoShape $data
     */
    public function withSocialMetaTagInfo(SocialMetaTagInfo|array $data): self
    {
        $info = $data instanceof SocialMetaTagInfo ? $data : SocialMetaTagInfo::fromArray($data);

        $data = $this->data;
        $data['dynamicLinkInfo']['socialMetaTagInfo'] = Json::decode(Json::encode($info), true);

        return new self($data);
    }

    public function withUnguessableSuffix(): self
    {
        $data = $this->data;
        $data['suffix']['option'] = self::WITH_UNGUESSABLE_SUFFIX;

        return new self($data);
    }

    public function withShortSuffix(): self
    {
        $data = $this->data;
        $data['suffix']['option'] = self::WITH_SHORT_SUFFIX;

        return new self($data);
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
