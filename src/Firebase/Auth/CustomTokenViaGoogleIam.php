<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use Firebase\Auth\Token\Domain\Generator;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Util\JSON;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Token;

class CustomTokenViaGoogleIam implements Generator
{
    /**
     * @var string
     */
    private $clientEmail;

    /**
     * @var ClientInterface
     */
    private $client;

    public function __construct(string $clientEmail, ClientInterface $client)
    {
        $this->clientEmail = $clientEmail;
        $this->client = $client;
    }

    public function createCustomToken($uid, array $claims = [], \DateTimeInterface $expiresAt = null): Token
    {
        $now = \time();
        $expiration = $expiresAt ? $expiresAt->getTimestamp() : $now + (60 * 60);

        $builder = (new Builder())
            ->setHeader('alg', 'RS256')
            ->set('uid', (string) $uid)
            ->setIssuer($this->clientEmail)
            ->setSubject($this->clientEmail)
            ->setIssuedAt($now)
            ->setExpiration($expiration)
            ->setAudience('https://identitytoolkit.googleapis.com/google.identity.identitytoolkit.v1.IdentityToolkit');

        if (!empty($claims)) {
            $builder->set('claims', $claims);
        }

        $token = $builder->getToken();

        $url = 'https://iam.googleapis.com/v1/projects/-/serviceAccounts/'.$this->clientEmail.':signBlob';

        try {
            $response = $this->client->request('POST', $url, [
                'json' => [
                    'bytesToSign' => \base64_encode($token->getPayload()),
                ],
            ]);
        } catch (RequestException $e) {
            throw AuthException::fromRequestException($e);
        }

        $result = JSON::decode((string) $response->getBody(), true);

        if ($base64EncodedSignature = $result['signature'] ?? null) {
            return (new Parser())->parse(((string) $token).$base64EncodedSignature);
        }

        throw new AuthException('Unable to create custom token.');
    }
}
