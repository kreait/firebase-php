<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use JsonSerializable;
use Stringable;

/**
 * @phpstan-import-type AndroidConfigShape from AndroidConfig
 * @phpstan-import-type ApnsConfigShape from ApnsConfig
 * @phpstan-import-type FcmOptionsShape from FcmOptions
 * @phpstan-import-type NotificationShape from Notification
 * @phpstan-import-type WebPushConfigShape from WebPushConfig
 *
 * @phpstan-type MessageInputShape array{
 *     token?: non-empty-string,
 *     topic?: non-empty-string,
 *     condition?: non-empty-string,
 *     data?: MessageData|array<non-empty-string, Stringable|string>,
 *     notification?: Notification|NotificationShape,
 *     android?: AndroidConfigShape,
 *     apns?: ApnsConfig|ApnsConfigShape,
 *     webpush?: WebPushConfig|WebPushConfigShape,
 *     fcm_options?: FcmOptions|FcmOptionsShape
 * }
 * @phpstan-type MessageOutputShape array{
 *     token?: non-empty-string,
 *     topic?: non-empty-string,
 *     condition?: non-empty-string,
 *     data?: array<non-empty-string, string>,
 *     notification?: NotificationShape,
 *     android?: AndroidConfigShape,
 *     apns?: ApnsConfigShape,
 *     webpush?: WebPushConfigShape,
 *     fcm_options?: FcmOptionsShape
 * }
 */
interface Message extends JsonSerializable
{
}
