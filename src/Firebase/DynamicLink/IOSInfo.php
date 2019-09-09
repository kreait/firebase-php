<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink;

use JsonSerializable;

final class IOSInfo implements JsonSerializable
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
     * The bundle ID of the iOS app to use to open the link. The app must be connected to your project from the
     * Overview page of the Firebase console. Required for the Dynamic Link to open an iOS app.
     */
    public function withBundleId(string $bundleId): self
    {
        $info = clone $this;
        $info->data['iosBundleId'] = $bundleId;

        return $info;
    }

    /**
     * The link to open when the app isn't installed. Specify this to do something other than install your app from the
     * App Store when the app isn't installed, such as open the mobile web version of the content, or display a
     * promotional page for your app.
     */
    public function withFallbackLink(string $fallbackLink): self
    {
        $info = clone $this;
        $info->data['iosFallbackLink'] = $fallbackLink;

        return $info;
    }

    /**
     * Your app's custom URL scheme, if defined to be something other than your app's bundle ID.
     */
    public function withCustomScheme(string $customScheme): self
    {
        $info = clone $this;
        $info->data['iosCustomScheme'] = $customScheme;

        return $info;
    }

    /**
     * The link to open on iPads when the app isn't installed. Specify this to do something other than install your
     * app from the App Store when the app isn't installed, such as open the web version of the content, or
     * display a promotional page for your app.
     */
    public function withIPadFallbackLink(string $ipadFallbackLink): self
    {
        $info = clone $this;
        $info->data['iosIpadFallbackLink'] = $ipadFallbackLink;

        return $info;
    }

    /**
     * The bundle ID of the iOS app to use on iPads to open the link. The app must be connected to your project from
     * the Overview page of the Firebase console.
     */
    public function withIPadBundleId(string $iPadBundleId): self
    {
        $info = clone $this;
        $info->data['iosIpadBundleId'] = $iPadBundleId;

        return $info;
    }

    /**
     * Your app's App Store ID, used to send users to the App Store when the app isn't installed.
     */
    public function withAppStoreId(string $appStoreId): self
    {
        $info = clone $this;
        $info->data['iosAppStoreId'] = $appStoreId;

        return $info;
    }

    public function jsonSerialize()
    {
        return $this->data;
    }
}
