<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging\Processor;

use Beste\Json;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Message;
use Kreait\Firebase\Messaging\MessageData;
use Kreait\Firebase\Messaging\Notification;

use function array_key_exists;
use function is_array;

/**
 * @internal
 *
 * @phpstan-import-type ApnsConfigShape from ApnsConfig
 * @phpstan-import-type NotificationShape from Notification
 */
final class SetApnsPushTypeIfNeeded
{
    public function __invoke(Message $message): Message
    {
        $payload = Json::decode(Json::encode($message), true);

        $notification = $this->getNotification($payload);
        $messageData = $this->getMessageData($payload);
        $apnsConfig = $this->getApnsConfig($payload);
        $apnsData = $apnsConfig->data();

        $isAlert = $notification !== null || $apnsConfig->isAlert();
        $hasData = $messageData->toArray() !== [] || $apnsData !== [];
        $isBackgroundMessage = !$isAlert && $hasData;

        if (!$isAlert && !$hasData) {
            return $message;
        }

        if ($apnsConfig->hasHeader('apns-push-type')) {
            return $message;
        }

        if ($isAlert) {
            $apnsConfig = $apnsConfig->withHeader('apns-push-type', 'alert');
        } elseif ($isBackgroundMessage) {
            $apnsConfig = $apnsConfig->withHeader('apns-push-type', 'background');
        }

        return CloudMessage::fromArray($payload)->withApnsConfig($apnsConfig);
    }

    /**
     * @param array<string, array<string, string>> $payload
     */
    public function getNotification(array $payload): ?Notification
    {
        // @phpstan-ignore-next-line
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
     * @param array{
     *     data?: array<non-empty-string, string>
     * } $payload
     */
    public function getMessageData(array $payload): MessageData
    {
        if (!array_key_exists('data', $payload)) {
            return MessageData::fromArray([]);
        }

        // @phpstan-ignore-next-line
        if (!is_array($payload['data'])) {
            return MessageData::fromArray([]);
        }

        return MessageData::fromArray($payload['data']);
    }
}
