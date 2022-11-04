<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging\Http\Request;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Kreait\Firebase\Http\HasSubRequests;
use Kreait\Firebase\Http\Requests;
use Kreait\Firebase\Http\RequestWithSubRequests;
use Kreait\Firebase\Http\WrappedPsr7Request;
use Kreait\Firebase\Messaging\Messages;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 */
final class SendMessages implements HasSubRequests, RequestInterface
{
    use WrappedPsr7Request;

    public function __construct(string $projectId, Messages $messages, bool $validateOnly = false)
    {
        if ($messages->count() > Messaging::BATCH_MESSAGE_LIMIT) {
            throw new InvalidArgument('Only '.Messaging::BATCH_MESSAGE_LIMIT.' can be sent at a time.');
        }

        $subRequests = [];

        $index = 0;

        foreach ($messages as $message) {
            $subRequests[] = (new SendMessage($projectId, $message, $validateOnly))
                // see https://github.com/firebase/firebase-admin-node/blob/master/src/messaging/batch-request.ts#L104
                ->withHeader('Content-ID', (string) ++$index)
                ->withHeader('Content-Transfer-Encoding', 'binary')
                ->withHeader('Content-Type', 'application/http');
        }

        $this->wrappedRequest = new RequestWithSubRequests(
            'https://fcm.googleapis.com/batch',
            new Requests(...$subRequests),
        );
    }
}
