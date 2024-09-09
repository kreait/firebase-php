<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging\Processor;

use Beste\Json;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Message;
use Kreait\Firebase\Messaging\MessageData;
use Kreait\Firebase\Messaging\Notification;

use function is_array;

/**
 * @internal
 *
 * @phpstan-import-type ApnsConfigShape from ApnsConfig
 * @phpstan-import-type NotificationShape from Notification
 */
final class SetApnsContentAvailableIfNeeded
{
    public function __invoke(Message $message): Message
    {
        $payload = Json::decode(Json::encode($message), true);

        $notification = $this->getNotification($payload);
        $apnsConfig = $this->getApnsConfig($payload);
        $isAlert = $notification !== null || $apnsConfig->isAlert();

        if ($isAlert) {
            return $message;
        }

        $messageData = $this->getMessageData($payload);
        $apnsData = $apnsConfig->data();

        $hasData = $messageData->toArray() !== [] || $apnsData !== [];

        if (!$hasData) {
            // No data, no 'content-available' field
            return $message;
        }

        $apnsConfig = $apnsConfig->withApsField('content-available', 1);

        return CloudMessage::fromArray($payload)->withApnsConfig($apnsConfig);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function getNotification(array $payload): ?Notification
    {
        $notification = $payload['notification'] ?? null;

        if (is_array($notification)) {
            return Notification::fromArray($notification);
        }

        return null;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function getApnsConfig(array $payload): ApnsConfig
    {
        $apnsConfig = $payload['apns'] ?? [];

        if (is_array($apnsConfig)) {
            return ApnsConfig::fromArray($apnsConfig);
        }

        return ApnsConfig::new();
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function getMessageData(array $payload): MessageData
    {
        $data = $payload['data'] ?? null;

        if (!is_array($data)) {
            return MessageData::fromArray([]);
        }

        return MessageData::fromArray($data);
    }
}
