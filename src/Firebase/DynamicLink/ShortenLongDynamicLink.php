<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink;

use JsonSerializable;
use Kreait\Firebase\Value\Url;
use Psr\Http\Message\UriInterface;

final class ShortenLongDynamicLink implements JsonSerializable
{
    const WITH_UNGUESSABLE_SUFFIX = 'UNGUESSABLE';
    const WITH_SHORT_SUFFIX = 'SHORT';

    private $data = [
        'suffix' => ['option' => self::WITH_UNGUESSABLE_SUFFIX],
    ];

    private function __construct()
    {
    }

    /**
     * The long dynamic link that has been created as described in {@see https://firebase.google.com/docs/dynamic-links/create-manually}.
     *
     * @param string|UriInterface|Url $url
     */
    public static function forLongDynamicLink($url): self
    {
        $url = Url::fromValue((string) $url);

        $action = new self();
        $action->data['longDynamicLink'] = (string) $url;

        return $action;
    }

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

    public function jsonSerialize()
    {
        return $this->data;
    }
}
