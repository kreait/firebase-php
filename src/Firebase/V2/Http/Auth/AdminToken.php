<?php

namespace Firebase\V2\Http\Auth;

use Firebase\V2\Http\Auth;
use GuzzleHttp\Psr7;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Psr\Http\Message\RequestInterface;

class AdminToken implements Auth
{
    /**
     * @var string
     */
    private $secret;

    public function __construct(string $databaseSecret)
    {
        $this->secret = $databaseSecret;
    }

    public function authenticateRequest(RequestInterface $request): RequestInterface
    {
        $uri = $request->getUri();

        $now = time();

        $token = (new Builder())
            ->setIssuedAt($now)
            ->setExpiration($now + (60 * 60))
            ->set('admin', true)
            ->sign(new Sha256(), $this->secret)
            ->getToken();

        $queryParams = ['auth' => (string) $token] + Psr7\parse_query($uri->getQuery());
        $queryString = Psr7\build_query($queryParams);

        $newUri = $uri->withQuery($queryString);

        return $request->withUri($newUri);
    }
}
