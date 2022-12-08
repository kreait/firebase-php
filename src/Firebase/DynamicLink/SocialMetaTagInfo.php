<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink;

use JsonSerializable;

/**
 * @phpstan-type SocialMetaTagInfoShape array{
 *     socialTitle?: non-empty-string,
 *     socialDescription?: non-empty-string,
 *     socialImageLink?: non-empty-string
 * }
 */
final class SocialMetaTagInfo implements JsonSerializable
{
    /**
     * @param SocialMetaTagInfoShape $data
     */
    private function __construct(private readonly array $data)
    {
    }

    /**
     * @param SocialMetaTagInfoShape $data
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
     * The title to use when the Dynamic Link is shared in a social post.
     *
     * @param non-empty-string $title
     */
    public function withTitle(string $title): self
    {
        $data = $this->data;
        $data['socialTitle'] = $title;

        return new self($data);
    }

    /**
     * The description to use when the Dynamic Link is shared in a social post.
     *
     * @param non-empty-string $description
     */
    public function withDescription(string $description): self
    {
        $data = $this->data;
        $data['socialDescription'] = $description;

        return new self($data);
    }

    /**
     * The URL to an image related to this link.
     *
     * @param non-empty-string $imageLink
     */
    public function withImageLink(string $imageLink): self
    {
        $data = $this->data;
        $data['socialImageLink'] = $imageLink;

        return new self($data);
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
