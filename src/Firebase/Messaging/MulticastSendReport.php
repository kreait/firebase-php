<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Countable;

use function array_filter;
use function array_map;
use function array_values;
use function count;

final class MulticastSendReport implements Countable
{
    /**
     * @var array<SendReport>
     */
    private array $items = [];

    private function __construct()
    {
    }

    /**
     * @param SendReport[] $items
     */
    public static function withItems(array $items): self
    {
        $report = new self();
        $report->items = $items;

        return $report;
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
        return $this->filter(static fn(SendReport $item): bool => $item->isSuccess());
    }

    public function failures(): self
    {
        return $this->filter(static fn(SendReport $item): bool => $item->isFailure());
    }

    public function hasFailures(): bool
    {
        return $this->failures()->count() > 0;
    }

    public function filter(callable $callback): self
    {
        $items = $this->items;

        return self::withItems(array_values(array_filter($items, $callback)));
    }

    /**
     * @return list<mixed>
     */
    public function map(callable $callback): array
    {
        return array_values(array_map($callback, $this->items));
    }

    /**
     * @return list<non-empty-string>
     */
    public function validTokens(): array
    {
        return $this->successes()
            ->filter(static fn(SendReport $report): bool => $report->target()->type() === MessageTarget::TOKEN)
            ->map(static fn(SendReport $report): string => $report->target()->value())
        ;
    }

    /**
     * Returns all provided registration tokens that were not reachable.
     *
     * @return list<non-empty-string>
     */
    public function unknownTokens(): array
    {
        return $this
            ->filter(static fn(SendReport $report): bool => $report->messageWasSentToUnknownToken())
            ->map(static fn(SendReport $report): string => $report->target()->value())
        ;
    }

    /**
     * Returns all provided registration tokens that were invalid.
     *
     * @return list<non-empty-string>
     */
    public function invalidTokens(): array
    {
        return $this
            ->filter(static fn(SendReport $report): bool => $report->messageTargetWasInvalid())
            ->map(static fn(SendReport $report): string => $report->target()->value())
        ;
    }

    public function count(): int
    {
        return count($this->items);
    }
}
