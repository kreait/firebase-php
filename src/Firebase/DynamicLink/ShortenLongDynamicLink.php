<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink;

use JsonSerializable;
use Kreait\Firebase\Value\Url;

final class ShortenLongDynamicLink implements JsonSerializable
{
    public const WITH_UNGUESSABLE_SUFFIX = 'UNGUESSABLE';
    public const WITH_SHORT_SUFFIX = 'SHORT';

    /** @var array<string, mixed> */
    private array $data = [
        'suffix' => ['option' => self::WITH_UNGUESSABLE_SUFFIX],
    ];

    private function __construct()
    {
    }

    /**
     * The long dynamic link that has been created as described in {@see https://firebase.google.com/docs/dynamic-links/create-manually}.
     *
     * @param \Stringable|string $url
     */
    public static function forLongDynamicLink($url): self
    {
        $action = new self();
        $action->data['longDynamicLink'] = (string) Url::fromValue((string) $url);

        return $action;
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
