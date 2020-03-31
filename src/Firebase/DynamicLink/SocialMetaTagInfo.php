<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink;

use JsonSerializable;

final class SocialMetaTagInfo implements JsonSerializable
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
     * The title to use when the Dynamic Link is shared in a social post.
     */
    public function withTitle(string $title): self
    {
        $info = clone $this;
        $info->data['socialTitle'] = $title;

        return $info;
    }

    /**
     * The description to use when the Dynamic Link is shared in a social post.
     */
    public function withDescription(string $description): self
    {
        $info = clone $this;
        $info->data['socialDescription'] = $description;

        return $info;
    }

    /**
     * The URL to an image related to this link.
     */
    public function withImageLink(string $imageLink): self
    {
        $info = clone $this;
        $info->data['socialImageLink'] = $imageLink;

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
