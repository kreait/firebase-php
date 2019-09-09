<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink;

use JsonSerializable;

final class AndroidInfo implements JsonSerializable
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
     * The package name of the Android app to use to open the link. The app must be connected to your project from the
     * Overview page of the Firebase console. Required for the Dynamic Link to open an Android app.
     */
    public function withPackageName(string $packageName): self
    {
        $info = clone $this;
        $info->data['androidPackageName'] = $packageName;

        return $info;
    }

    /**
     * The link to open when the app isn't installed. Specify this to do something other than install your app
     * from the Play Store when the app isn't installed, such as open the mobile web version of the content,
     * or display a promotional page for your app.
     */
    public function withFallbackLink(string $fallbackLink): self
    {
        $info = clone $this;
        $info->data['androidFallbackLink'] = $fallbackLink;

        return $info;
    }

    /**
     * The versionCode of the minimum version of your app that can open the link. If the installed app is an older
     * version, the user is taken to the Play Store to upgrade the app.
     *
     * @see http://developer.android.com/tools/publishing/versioning.html#appversioning
     */
    public function withMinPackageVersionCode(string $minPackageVersionCode): self
    {
        $info = clone $this;
        $info->data['androidMinPackageVersionCode'] = $minPackageVersionCode;

        return $info;
    }

    public function jsonSerialize()
    {
        return $this->data;
    }
}
