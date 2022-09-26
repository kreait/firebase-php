<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging\Http\Request;

use Beste\Json;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use Kreait\Firebase\Http\WrappedPsr7Request;
use Kreait\Firebase\Messaging\Message;
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

        $payload = ['message' => $message];

        if ($validateOnly === true) {
            $payload['validate_only'] = true;
        }

        $body = Utils::streamFor(Json::encode($payload));
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
