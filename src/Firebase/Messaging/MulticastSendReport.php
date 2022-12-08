<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Beste\Json;
use Countable;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Exception\MessagingApiExceptionConverter;
use Kreait\Firebase\Http\Requests;
use Kreait\Firebase\Http\Responses;
use Kreait\Firebase\Messaging\Http\Request\MessageRequest;
use Psr\Http\Message\RequestInterface;

use function array_filter;
use function array_map;
use function array_pop;
use function array_values;
use function count;
use function explode;

final class MulticastSendReport implements Countable
{
    /** @var array<SendReport> */
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
     * @internal
     */
    public static function fromRequestsAndResponses(Requests $requests, Responses $responses): self
    {
        $reports = [];
        $errorHandler = new MessagingApiExceptionConverter();

        foreach ($responses as $response) {
            $contentIdHeader = $response->getHeaderLine('Content-ID');
            $contentIdHeaderParts = explode('-', $contentIdHeader);

            if (!($responseId = array_pop($contentIdHeaderParts) ?: null)) {
                continue;
            }

            $matchingRequest = $requests->findByContentId($responseId);

            if (!$matchingRequest instanceof RequestInterface) {
                continue;
            }

            try {
                $requestData = Json::decode((string) $matchingRequest->getBody(), true);
            } catch (InvalidArgumentException) {
                continue;
            }

            $messageTargetType = MessageTarget::UNKNOWN;
            $messageTargetValue = 'unknown';

            if ($token = $requestData['message']['token'] ?? null) {
                $messageTargetType = MessageTarget::TOKEN;
                $messageTargetValue = (string) $token;
            } elseif ($topic = $requestData['message']['topic'] ?? null) {
                $messageTargetType = MessageTarget::TOPIC;
                $messageTargetValue = (string) $topic;
            } elseif ($condition = $requestData['message']['condition'] ?? null) {
                $messageTargetType = MessageTarget::CONDITION;
                $messageTargetValue = (string) $condition;
            }

            if ($messageTargetValue === '') {
                continue;
            }

            $target = MessageTarget::with($messageTargetType, $messageTargetValue);

            $message = $matchingRequest instanceof MessageRequest
                ? $matchingRequest->message()
                : null;

            if ($response->getStatusCode() < 400) {
                try {
                    $responseData = Json::decode((string) $response->getBody(), true);
                } catch (InvalidArgumentException) {
                    $responseData = [];
                }

                $reports[] = SendReport::success($target, $responseData, $message);
            } else {
                $error = $errorHandler->convertResponse($response);
                $reports[] = SendReport::failure($target, $error, $message);
            }
        }

        return self::withItems($reports);
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
        return $this->filter(static fn (SendReport $item) => $item->isSuccess());
    }

    public function failures(): self
    {
        return $this->filter(static fn (SendReport $item) => $item->isFailure());
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
            ->filter(static fn (SendReport $report) => $report->target()->type() === MessageTarget::TOKEN)
            ->map(static fn (SendReport $report) => $report->target()->value());
    }

    /**
     * Returns all provided registration tokens that were not reachable.
     *
     * @return list<non-empty-string>
     */
    public function unknownTokens(): array
    {
        return $this
            ->filter(static fn (SendReport $report) => $report->messageWasSentToUnknownToken())
            ->map(static fn (SendReport $report) => $report->target()->value());
    }

    /**
     * Returns all provided registration tokens that were invalid.
     *
     * @return list<non-empty-string>
     */
    public function invalidTokens(): array
    {
        return $this
            ->filter(static fn (SendReport $report) => $report->messageTargetWasInvalid())
            ->map(static fn (SendReport $report) => $report->target()->value());
    }

    public function count(): int
    {
        return count($this->items);
    }
}
