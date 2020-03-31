<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink;

use JsonSerializable;

final class NavigationInfo implements JsonSerializable
{
    /** @var array<string, mixed> */
    private $data = [];

    private function __construct()
    {
    }

    /**
     * @param array<string, mixed> $data
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
     * If set, skip the app preview page when the Dynamic Link is opened, and instead redirect to the app
     * or store. The app preview page (enabled by default) can more reliably send users to the most appropriate
     * destination when they open Dynamic Links in apps; however, if you expect a Dynamic Link to be opened
     * only in apps that can open Dynamic Links reliably without this page, you can disable it with this
     * parameter. Note: the app preview page is only shown on iOS currently, but may eventually be
     * shown on Android. This parameter will affect the behavior of the Dynamic Link on both
     * platforms.
     */
    public function withForcedRedirect(): self
    {
        $info = clone $this;
        $info->data['enableForcedRedirect'] = true;

        return $info;
    }

    /**
     * @see withForcedRedirect()
     */
    public function withoutForcedRedirect(): self
    {
        $info = clone $this;
        unset($info->data['enableForcedRedirect']);

        return $info;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
