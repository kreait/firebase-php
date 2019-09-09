<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink;

use JsonSerializable;
use Kreait\Firebase\Value\Url;
use Psr\Http\Message\UriInterface;

final class CreateDynamicLink implements JsonSerializable
{
    const WITH_UNGUESSABLE_SUFFIX = 'UNGUESSABLE';
    const WITH_SHORT_SUFFIX = 'SHORT';

    private $data = [
        'dynamicLinkInfo' => [],
        'suffix' => ['option' => self::WITH_UNGUESSABLE_SUFFIX],
    ];

    private function __construct()
    {
    }

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
     * @param string|UriInterface|Url $url
     */
    public static function forUrl($url): self
    {
        $url = Url::fromValue((string) $url);

        $action = new self();
        $action->data['dynamicLinkInfo']['link'] = (string) $url;

        return $action;
    }

    /**
     * @param string|Url|UriInterface $dynamicLinkDomain
     */
    public function withDynamicLinkDomain($dynamicLinkDomain): self
    {
        $dynamicLinkDomain = Url::fromValue((string) $dynamicLinkDomain);

        $action = clone $this;
        $action->data['dynamicLinkInfo']['domainUriPrefix'] = (string) $dynamicLinkDomain;

        return $action;
    }

    public function hasDynamicLinkDomain(): bool
    {
        return (bool) ($this->data['dynamicLinkInfo']['domainUriPrefix'] ?? null);
    }

    public function withAnalyticsInfo($data): self
    {
        $info = $data instanceof AnalyticsInfo ? $data : AnalyticsInfo::fromArray($data);

        $action = clone $this;
        $action->data['dynamicLinkInfo']['analyticsInfo'] = $info;

        return $action;
    }

    public function withAndroidInfo($data): self
    {
        $info = $data instanceof AndroidInfo ? $data : AndroidInfo::fromArray($data);

        $action = clone $this;
        $action->data['dynamicLinkInfo']['androidInfo'] = $info->jsonSerialize();

        return $action;
    }

    public function withIOSInfo($data): self
    {
        $info = $data instanceof IOSInfo ? $data : IOSInfo::fromArray($data);

        $action = clone $this;
        $action->data['dynamicLinkInfo']['iosInfo'] = $info;

        return $action;
    }

    public function withNavigationInfo($data): self
    {
        $info = $data instanceof NavigationInfo ? $data : NavigationInfo::fromArray($data);

        $action = clone $this;
        $action->data['dynamicLinkInfo']['navigationInfo'] = $info->jsonSerialize();

        return $action;
    }

    public function withSocialMetaTagInfo($data): self
    {
        $info = $data instanceof SocialMetaTagInfo ? $data : SocialMetaTagInfo::fromArray($data);

        $action = clone $this;
        $action->data['dynamicLinkInfo']['socialMetaTagInfo'] = $info;

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

    public function jsonSerialize()
    {
        return $this->data;
    }
}
