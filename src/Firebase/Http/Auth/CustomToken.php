<?php

namespace Kreait\Firebase\Http\Auth;

use GuzzleHttp\Psr7;
use Kreait\Firebase\Http\AuthenticationMethod;
use Kreait\Firebase\Util\JSON;
use Psr\Http\Message\RequestInterface;

final class CustomToken implements AuthenticationMethod
{
    /**
     * @var string
     */
    private $token;

    public function __construct(string $uid, array $claims = [])
    {
        $claims = array_filter($claims, function ($value) {
            return null !== $value;
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
