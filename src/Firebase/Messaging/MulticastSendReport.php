<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Countable;

final class MulticastSendReport implements Countable
{
    /** @var SendReport[] */
    private $items = [];

    private function __construct()
    {
    }

    /**
     * @param SendReport[] $items
     */
    public static function withItems(array $items): self
    {
        $report = new self();

        foreach ($items as $item) {
            $report = $report->withAdded($item);
        }

        return $report;
    }

    public function withAdded(SendReport $report): self
    {
        $new = clone $this;
        $new->items[] = $report;

        return $new;
    }

    /**
     * @return SendReport[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function successes(): self
    {
        return self::withItems(\array_filter($this->items, static function (SendReport $item) {
            return $item->isSuccess();
        }));
    }

    public function failures(): self
    {
        return self::withItems(\array_filter($this->items, static function (SendReport $item) {
            return $item->isFailure();
        }));
    }

    public function hasFailures(): bool
    {
        return $this->failures()->count() > 0;
    }

    public function count(): int
    {
        return \count($this->items);
    }
}
