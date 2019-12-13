<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth\CreateActionLink;

use GuzzleHttp\Psr7\Request;
use function GuzzleHttp\Psr7\stream_for;
use function GuzzleHttp\Psr7\uri_for;
use Kreait\Firebase\Auth\CreateActionLink;
use Kreait\Firebase\Http\WrappedPsr7Request;
use Psr\Http\Message\RequestInterface;

final class ApiRequest implements RequestInterface
{
    use WrappedPsr7Request;

    public function __construct(CreateActionLink $action)
    {
        $uri = uri_for('https://identitytoolkit.googleapis.com/v1/accounts:sendOobCode');

        $data = [
            'requestType' => $action->type(),
            'email' => $action->email(),
            'returnOobLink' => true,
        ] + $action->settings()->toArray();

        $body = stream_for(\json_encode($data));

        $headers = \array_filter([
            'Content-Type' => 'application/json; charset=UTF-8',
            'Content-Length' => $body->getSize(),
        ]);

        $this->wrappedRequest = new Request('POST', $uri, $headers, $body);
    }
}
