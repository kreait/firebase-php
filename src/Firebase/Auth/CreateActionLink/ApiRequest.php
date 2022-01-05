<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth\CreateActionLink;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use Kreait\Firebase\Auth\CreateActionLink;
use Kreait\Firebase\Http\WrappedPsr7Request;
use Kreait\Firebase\Util\JSON;
use Psr\Http\Message\RequestInterface;

final class ApiRequest implements RequestInterface
{
    use WrappedPsr7Request;

    public function __construct(CreateActionLink $action)
    {
        $uri = Utils::uriFor('https://identitytoolkit.googleapis.com/v1/accounts:sendOobCode');

        $data = \array_filter([
            'requestType' => $action->type(),
            'email' => $action->email(),
            'returnOobLink' => true,
            'tenantId' => $action->tenantId(),
        ]) + $action->settings()->toArray();

        $body = Utils::streamFor(JSON::encode($data, JSON_FORCE_OBJECT));

        $headers = \array_filter([
            'Content-Type' => 'application/json; charset=UTF-8',
            'Content-Length' => (string) $body->getSize(),
            'X-Firebase-Locale' => $action->locale(),
        ]);

        $this->wrappedRequest = new Request('POST', $uri, $headers, $body);
    }
}
