<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging\Http\Request;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use Kreait\Firebase\Http\WrappedPsr7Request;
use Kreait\Firebase\Messaging\Message;
use Kreait\Firebase\Util\JSON;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 */
final class SendMessage implements MessageRequest, RequestInterface
{
    use WrappedPsr7Request;

    private Message $message;

    public function __construct(string $projectId, Message $message, bool $validateOnly = false)
    {
        $uri = Utils::uriFor('https://fcm.googleapis.com/v1/projects/'.$projectId.'/messages:send');
        $body = Utils::streamFor(JSON::encode(['message' => $message, 'validate_only' => $validateOnly]));
        $headers = [
            'Content-Type' => 'application/json; charset=UTF-8',
            'Content-Length' => (string) $body->getSize(),
        ];

        $this->wrappedRequest = new Request('POST', $uri, $headers, $body);
        $this->message = $message;
    }

    public function message(): Message
    {
        return $this->message;
    }
}
