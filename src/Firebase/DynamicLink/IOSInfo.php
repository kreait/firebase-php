<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink;

use JsonSerializable;

/**
 * @phpstan-type IOSInfoShape array{
 *     iosBundleId?: non-empty-string,
 *     iosFallbackLink?: non-empty-string,
 *     iosCustomScheme?: non-empty-string,
 *     iosIpadFallbackLink?: non-empty-string,
 *     iosIpadBundleId?: non-empty-string,
 *     iosAppStoreId?: non-empty-string
 * }
 */
final class IOSInfo implements JsonSerializable
{
    /**
     * @param IOSInfoShape $data
     */
    private function __construct(private readonly array $data)
    {
    }

    /**
     * @param IOSInfoShape $data
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
     * The bundle ID of the iOS app to use to open the link. The app must be connected to your project from the
     * Overview page of the Firebase console. Required for the Dynamic Link to open an iOS app.
     *
     * @param non-empty-string $bundleId
     */
    public function withBundleId(string $bundleId): self
    {
        $data = $this->data;
        $data['iosBundleId'] = $bundleId;

        return new self($data);
    }

    /**
     * The link to open when the app isn't installed. Specify this to do something other than install your app from the
     * App Store when the app isn't installed, such as open the mobile web version of the content, or display a
     * promotional page for your app.
     *
     * @param non-empty-string $fallbackLink
     */
    public function withFallbackLink(string $fallbackLink): self
    {
        $data = $this->data;
        $data['iosFallbackLink'] = $fallbackLink;

        return new self($data);
    }

    /**
     * Your app's custom URL scheme, if defined to be something other than your app's bundle ID.
     *
     * @param non-empty-string $customScheme
     */
    public function withCustomScheme(string $customScheme): self
    {
        $data = $this->data;
        $data['iosCustomScheme'] = $customScheme;

        return new self($data);
    }

    /**
     * The link to open on iPads when the app isn't installed. Specify this to do something other than install your
     * app from the App Store when the app isn't installed, such as open the web version of the content, or
     * display a promotional page for your app.
     *
     * @param non-empty-string $ipadFallbackLink
     */
    public function withIPadFallbackLink(string $ipadFallbackLink): self
    {
        $data = $this->data;
        $data['iosIpadFallbackLink'] = $ipadFallbackLink;

        return new self($data);
    }

    /**
     * The bundle ID of the iOS app to use on iPads to open the link. The app must be connected to your project from
     * the Overview page of the Firebase console.
     *
     * @param non-empty-string $iPadBundleId
     */
    public function withIPadBundleId(string $iPadBundleId): self
    {
        $data = $this->data;
        $data['iosIpadBundleId'] = $iPadBundleId;

        return new self($data);
    }

    /**
     * Your app's App Store ID, used to send users to the App Store when the app isn't installed.
     *
     * @param non-empty-string $appStoreId
     */
    public function withAppStoreId(string $appStoreId): self
    {
        $data = $this->data;
        $data['iosAppStoreId'] = $appStoreId;

        return new self($data);
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
