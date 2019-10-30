<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging\Http\Request;

use GuzzleHttp\Psr7\Request;
use function GuzzleHttp\Psr7\stream_for;
use function GuzzleHttp\Psr7\uri_for;
use Kreait\Firebase\Http\WrappedPsr7Request;
use Kreait\Firebase\Messaging\Message;
use Psr\Http\Message\RequestInterface;

final class SendMessage implements RequestInterface
{
    use WrappedPsr7Request;

    public function __construct(string $projectId, Message $message)
    {
        $uri = uri_for('https://fcm.googleapis.com/v1/projects/'.$projectId.'/messages:send');
        $body = stream_for(\json_encode(['message' => $message]));
        $headers = [
            'Content-Type' => 'application/json; charset=UTF-8',
            'Content-Length' => $body->getSize(),
        ];

        $this->wrappedRequest = new Request('POST', $uri, $headers, $body);
    }
}
