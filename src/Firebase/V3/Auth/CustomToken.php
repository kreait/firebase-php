<?php

namespace Firebase\V3\Auth;

use Firebase\Util\JSON;
use Firebase\V3\Auth;
use GuzzleHttp\Psr7;
use Psr\Http\Message\RequestInterface;

final class CustomToken implements Auth
{
    /**
     * @var string
     */
    private $token;

    public function __construct(string $uid, array $claims = [])
    {
        $claims = array_filter($claims, function ($value) {
            return $value !== null;
        });

        $claims = ['uid' => $uid] + $claims;

        $this->token = JSON::encode($claims);
    }

    public function authenticateRequest(RequestInterface $request): RequestInterface
    {
        $uri = $request->getUri();

        $queryParams = ['auth_variable_override' => $this->token] + Psr7\parse_query($uri->getQuery());
        $queryString = Psr7\build_query($queryParams);

        $newUri = $uri->withQuery($queryString);

        return $request->withUri($newUri);
    }
}
