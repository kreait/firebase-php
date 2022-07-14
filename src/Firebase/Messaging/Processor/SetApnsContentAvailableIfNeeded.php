<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging\Processor;

use Beste\Json;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Message;
use Kreait\Firebase\Messaging\MessageData;
use Kreait\Firebase\Messaging\Notification;

/**
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

        if ($notification !== null || $apnsConfig->isAlert()) {
            // This is an alert, no 'content-available' field needed
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
     * @param array<string, array<string, string>> $payload
     */
    public function getNotification(array $payload): ?Notification
    {
        if (array_key_exists('notification', $payload) && is_array($payload['notification'])) {
            /** @var NotificationShape $notification */
            $notification = $payload['notification'];

            return Notification::fromArray($notification);
        }

        return null;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function getApnsConfig(array $payload): ApnsConfig
    {
        if (array_key_exists('apns', $payload) && is_array($payload['apns'])) {
            /** @var NotificationShape $config */
            $config = $payload['apns'];

            return ApnsConfig::fromArray($config);
        }

        return ApnsConfig::new();
    }

    /**
     * @param array<string, array<string, string>> $payload
     */
    public function getMessageData(array $payload): MessageData
    {
        if (array_key_exists('data', $payload) && is_array($payload['data'])) {
            $messageData = MessageData::fromArray($payload['data']);
        } else {
            $messageData = MessageData::fromArray([]);
        }
        return $messageData;
    }
}
