<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging\Http\Request;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use Kreait\Firebase\Http\WrappedPsr7Request;
use Kreait\Firebase\Messaging\Message;
use Psr\Http\Message\RequestInterface;

final class SendMessage implements RequestInterface
{
    use WrappedPsr7Request;

    public function __construct(string $projectId, Message $message, bool $validateOnly = false)
    {
        $uri = Utils::uriFor('https://fcm.googleapis.com/v1/projects/'.$projectId.'/messages:send');
        $body = Utils::streamFor(\json_encode(['message' => $message, 'validate_only' => $validateOnly]));
        $headers = [
            'Content-Type' => 'application/json; charset=UTF-8',
            'Content-Length' => $body->getSize(),
        ];

        $this->wrappedRequest = new Request('POST', $uri, $headers, $body);
    }
}
