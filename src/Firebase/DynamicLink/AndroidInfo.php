<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink;

use JsonSerializable;

/**
 * @phpstan-type AndroidInfoShape array{
 *     androidPackageName?: non-empty-string,
 *     androidFallbackLink?: non-empty-string,
 *     androidMinPackageVersionCode?: non-empty-string
 * }
 */
final class AndroidInfo implements JsonSerializable
{
    /**
     * @param AndroidInfoShape $data
     */
    private function __construct(private readonly array $data)
    {
    }

    /**
     * @param AndroidInfoShape $data
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
     * The package name of the Android app to use to open the link. The app must be connected to your project from the
     * Overview page of the Firebase console. Required for the Dynamic Link to open an Android app.
     *
     * @param non-empty-string $packageName
     */
    public function withPackageName(string $packageName): self
    {
        $data = $this->data;
        $data['androidPackageName'] = $packageName;

        return new self($data);
    }

    /**
     * The link to open when the app isn't installed. Specify this to do something other than install your app
     * from the Play Store when the app isn't installed, such as open the mobile web version of the content,
     * or display a promotional page for your app.
     *
     * @param non-empty-string $fallbackLink
     */
    public function withFallbackLink(string $fallbackLink): self
    {
        $data = $this->data;
        $data['androidFallbackLink'] = $fallbackLink;

        return new self($data);
    }

    /**
     * The versionCode of the minimum version of your app that can open the link. If the installed app is an older
     * version, the user is taken to the Play Store to upgrade the app.
     *
     * @see https://developer.android.com/studio/publish/versioning#appversioning
     *
     * @param non-empty-string $minPackageVersionCode
     */
    public function withMinPackageVersionCode(string $minPackageVersionCode): self
    {
        $data = $this->data;
        $data['androidMinPackageVersionCode'] = $minPackageVersionCode;

        return new self($data);
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
