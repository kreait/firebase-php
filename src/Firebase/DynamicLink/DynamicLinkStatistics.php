<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink;

use Kreait\Firebase\Util\JSON;
use Psr\Http\Message\ResponseInterface;

final class DynamicLinkStatistics
{
    /** @var array */
    private $rawData;

    /** @var EventStatistics */
    private $events;

    private function __construct()
    {
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

    public function rawData(): array
    {
        return $this->rawData;
    }
}
