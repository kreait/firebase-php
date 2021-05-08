<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink;

use Kreait\Firebase\Util\JSON;
use Psr\Http\Message\ResponseInterface;

final class DynamicLinkStatistics
{
    /** @var array<string, array<int, array<string, string>>> */
    private array $rawData = [];
    private EventStatistics $events;

    private function __construct()
    {
        $this->events = EventStatistics::fromArray([]);
    }

    /**
     * @internal
     */
    public static function fromApiResponse(ResponseInterface $response): self
    {
        $data = JSON::decode((string) $response->getBody(), true);

        $link = new self();
        $link->rawData = $data;
        $link->events = EventStatistics::fromArray($data['linkEventStats'] ?? []);

        return $link;
    }

    public function eventStatistics(): EventStatistics
    {
        return $this->events;
    }

    /**
     * @return array<string, array<int, array<string, string>>>
     */
    public function rawData(): array
    {
        return $this->rawData;
    }
}
