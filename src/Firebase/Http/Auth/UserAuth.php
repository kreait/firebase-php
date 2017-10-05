<?php

namespace Kreait\Firebase\Http\Auth;

use GuzzleHttp\Psr7;
use Kreait\Firebase\Auth\User;
use Kreait\Firebase\Http\Auth;
use Psr\Http\Message\RequestInterface;

final class UserAuth implements Auth
{
    /**
     * @var string
     */
    private $token;

    public function __construct(User $user)
    {
        $this->token = $user->getIdToken();
    }

    public function authenticateRequest(RequestInterface $request): RequestInterface
    {
        $uri = $request->getUri();

        $queryParams = ['auth' => $this->token] + Psr7\parse_query($uri->getQuery());
        $queryString = Psr7\build_query($queryParams);

        $newUri = $uri->withQuery($queryString);

        return $request->withUri($newUri);
    }
}
