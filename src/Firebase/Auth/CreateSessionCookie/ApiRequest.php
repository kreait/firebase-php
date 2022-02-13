<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth\CreateSessionCookie;

use Beste\Json;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use Kreait\Firebase\Auth\CreateSessionCookie;
use Kreait\Firebase\Http\WrappedPsr7Request;
use Psr\Http\Message\RequestInterface;

/**
 * @deprecated 6.0.1
 * @codeCoverageIgnore
 */
final class ApiRequest implements RequestInterface
{
    use WrappedPsr7Request;

    public function __construct(CreateSessionCookie $action)
    {
        $uri = Utils::uriFor('https://www.googleapis.com/identitytoolkit/v3/relyingparty/createSessionCookie');

        $data = [
            'idToken' => $action->idToken(),
            'validDuration' => $action->ttlInSeconds(),
        ];

        $body = Utils::streamFor(Json::encode($data, JSON_FORCE_OBJECT));

        $headers = \array_filter([
            'Content-Type' => 'application/json; charset=UTF-8',
            'Content-Length' => (string) $body->getSize(),
        ]);

        $this->wrappedRequest = new Request('POST', $uri, $headers, $body);
    }
}
