<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Countable;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Exception\MessagingApiExceptionConverter;
use Kreait\Firebase\Http\Requests;
use Kreait\Firebase\Http\Responses;
use Kreait\Firebase\Util\JSON;
use Psr\Http\Message\RequestInterface;

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

    public static function fromRequestsAndResponses(Requests $requests, Responses $responses): self
    {
        $reports = [];
        $errorHandler = new MessagingApiExceptionConverter();

        foreach ($responses as $response) {
            $contentIdHeader = $response->getHeaderLine('Content-ID');
            $contentIdHeaderParts = \explode('-', $contentIdHeader);

            if (!($responseId = \array_pop($contentIdHeaderParts) ?: null)) {
                continue;
            }

            $matchingRequest = $requests->findBy(static function (RequestInterface $request) use ($responseId) {
                $contentIdHeader = $request->getHeaderLine('Content-ID');
                $contentIdHeaderParts = \explode('-', $contentIdHeader);
                $contentId = \array_pop($contentIdHeaderParts);

                return $contentId === $responseId;
            });

            if (!$matchingRequest) {
                continue;
            }

            try {
                $requestData = JSON::decode((string) $matchingRequest->getBody(), true);
            } catch (InvalidArgumentException $e) {
                continue;
            }

            $target = null;

            if ($token = $requestData['message']['token'] ?? null) {
                $target = MessageTarget::with(MessageTarget::TOKEN, (string) $token);
            } elseif ($topic = $requestData['message']['topic'] ?? null) {
                $target = MessageTarget::with(MessageTarget::TOPIC, (string) $topic);
            } elseif ($condition = $requestData['message']['condition'] ?? null) {
                $target = MessageTarget::with(MessageTarget::CONDITION, (string) $condition);
            }

            if ($target === null) {
                continue;
            }

            if ($response->getStatusCode() < 400) {
                try {
                    $responseData = JSON::decode((string) $response->getBody(), true);
                } catch (InvalidArgumentException $e) {
                    $responseData = [];
                }

                $reports[] = SendReport::success($target, $responseData);
            } else {
                $error = $errorHandler->convertResponse($response);
                $reports[] = SendReport::failure($target, $error);
            }
        }

        return self::withItems($reports);
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
