<?php

namespace Firebase\V2\Http\Auth;

use Firebase\Token\TokenGenerator;
use Firebase\V2\Http\Auth;
use GuzzleHttp\Psr7;
use Psr\Http\Message\RequestInterface;

final class CustomToken implements Auth
{
    /**
     * @var string
     */
    private $token;

    public function __construct(string $databaseSecret, string $uid, array $claims = [])
    {
        $claims = array_filter($claims, function ($value) {
            return $value !== null;
        });

        $claims = ['uid' => $uid] + $claims;

        $this->token = (new TokenGenerator($databaseSecret))
            ->setData($claims)
            ->create();
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
