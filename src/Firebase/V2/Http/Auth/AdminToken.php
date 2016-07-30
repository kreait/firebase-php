<?php

namespace Firebase\V2\Http\Auth;

use Firebase\Token\TokenGenerator;
use Firebase\V2\Http\Auth;
use GuzzleHttp\Psr7;
use Psr\Http\Message\RequestInterface;

class AdminToken implements Auth
{
    /**
     * @var string
     */
    private $token;

    public function __construct(string $databaseSecret)
    {
        $options = ['admin' => true, 'debug' => true];

        $this->token = (new TokenGenerator($databaseSecret))
            ->setOptions($options)
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
