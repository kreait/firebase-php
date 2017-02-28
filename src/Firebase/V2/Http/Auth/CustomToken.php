<?php

namespace Firebase\V2\Http\Auth;

use Firebase\V2\Http\Auth;
use GuzzleHttp\Psr7;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac;
use Psr\Http\Message\RequestInterface;

final class CustomToken implements Auth
{
    /**
     * @var string
     */
    private $secret;

    /**
     * @var array
     */
    private $claims;

    public function __construct(string $databaseSecret, string $uid, array $claims = [])
    {
        $this->secret = $databaseSecret;

        $this->claims = ['uid' => $uid] + array_filter($claims, function ($value) {
            return $value !== null;
        });
    }

    public function authenticateRequest(RequestInterface $request): RequestInterface
    {
        $uri = $request->getUri();

        $now = time();

        $token = (new Builder())
            ->setIssuedAt($now)
            ->set('d', $this->claims)
            ->setExpiration($now + (60 * 60))
            ->sign(new Hmac\Sha256(), $this->secret)
            ->getToken();

        $queryParams = ['auth' => (string) $token] + Psr7\parse_query($uri->getQuery());
        $queryString = Psr7\build_query($queryParams);

        $newUri = $uri->withQuery($queryString);

        return $request->withUri($newUri);
    }
}
