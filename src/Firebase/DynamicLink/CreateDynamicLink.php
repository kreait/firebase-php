<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink;

use JsonSerializable;
use Kreait\Firebase\Value\Url;
use Stringable;

final class CreateDynamicLink implements JsonSerializable
{
    public const WITH_UNGUESSABLE_SUFFIX = 'UNGUESSABLE';
    public const WITH_SHORT_SUFFIX = 'SHORT';

    /** @var array<string, mixed> */
    private array $data = [
        'dynamicLinkInfo' => [],
        'suffix' => ['option' => self::WITH_UNGUESSABLE_SUFFIX],
    ];

    private function __construct()
    {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $action = new self();
        $action->data = $data;

        return $action;
    }

    public static function new(): self
    {
        return new self();
    }

    /**
     * The link your app will open. Specify a URL that your app can handle, typically the app's content
     * or payload, which initiates app-specific logic (such as crediting the user with a coupon or
     * displaying a welcome screen). This link must be a well-formatted URL, be properly
     * URL-encoded, use either HTTP or HTTPS, and cannot be another Dynamic Link.
     *
     * @param string|Stringable $url
     */
    public static function forUrl($url): self
    {
        $action = new self();
        $action->data['dynamicLinkInfo']['link'] = (string) Url::fromValue((string) $url);

        return $action;
    }

    /**
     * @param Stringable|string $dynamicLinkDomain
     */
    public function withDynamicLinkDomain($dynamicLinkDomain): self
    {
        $action = clone $this;
        $action->data['dynamicLinkInfo']['domainUriPrefix'] = (string) Url::fromValue((string) $dynamicLinkDomain);

        return $action;
    }

    public function hasDynamicLinkDomain(): bool
    {
        return (bool) ($this->data['dynamicLinkInfo']['domainUriPrefix'] ?? null);
    }

    /**
     * @param AnalyticsInfo|array<string, mixed> $data
     */
    public function withAnalyticsInfo($data): self
    {
        $info = $data instanceof AnalyticsInfo ? $data : AnalyticsInfo::fromArray($data);

        $action = clone $this;
        $action->data['dynamicLinkInfo']['analyticsInfo'] = $info->jsonSerialize();

        return $action;
    }

    /**
     * @param AndroidInfo|array<string, string> $data
     */
    public function withAndroidInfo($data): self
    {
        $info = $data instanceof AndroidInfo ? $data : AndroidInfo::fromArray($data);

        $action = clone $this;
        $action->data['dynamicLinkInfo']['androidInfo'] = $info->jsonSerialize();

        return $action;
    }

    /**
     * @param IOSInfo|array<string, string> $data
     */
    public function withIOSInfo($data): self
    {
        $info = $data instanceof IOSInfo ? $data : IOSInfo::fromArray($data);

        $action = clone $this;
        $action->data['dynamicLinkInfo']['iosInfo'] = $info->jsonSerialize();

        return $action;
    }

    /**
     * @param NavigationInfo|array<string, mixed> $data
     */
    public function withNavigationInfo($data): self
    {
        $info = $data instanceof NavigationInfo ? $data : NavigationInfo::fromArray($data);

        $action = clone $this;
        $action->data['dynamicLinkInfo']['navigationInfo'] = $info->jsonSerialize();

        return $action;
    }

    /**
     * @param SocialMetaTagInfo|array<string, mixed> $data
     */
    public function withSocialMetaTagInfo($data): self
    {
        $info = $data instanceof SocialMetaTagInfo ? $data : SocialMetaTagInfo::fromArray($data);

        $action = clone $this;
        $action->data['dynamicLinkInfo']['socialMetaTagInfo'] = $info->jsonSerialize();

        return $action;
    }

    public function withUnguessableSuffix(): self
    {
        $action = clone $this;
        $action->data['suffix']['option'] = self::WITH_UNGUESSABLE_SUFFIX;

        return $action;
    }

    public function withShortSuffix(): self
    {
        $action = clone $this;
        $action->data['suffix']['option'] = self::WITH_SHORT_SUFFIX;

        return $action;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
