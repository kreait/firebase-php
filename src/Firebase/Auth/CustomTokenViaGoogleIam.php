<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use Firebase\Auth\Token\Domain\Generator;
use GuzzleHttp\ClientInterface;
use InvalidArgumentException;
use Kreait\Firebase\Exception\Auth\AuthError;
use Kreait\Firebase\Exception\AuthApiExceptionConverter;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Util\JSON;
use Kreait\Firebase\Value\Uid;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Token;
use Throwable;

class CustomTokenViaGoogleIam implements Generator
{
    /** @var string */
    private $clientEmail;

    /** @var ClientInterface */
    private $client;

    public function __construct(string $clientEmail, ClientInterface $client)
    {
        $this->clientEmail = $clientEmail;
        $this->client = $client;
    }

    /**
     * @param Uid|string$uid
     * @param array<string, mixed> $claims
     *
     * @throws AuthException
     * @throws FirebaseException
     */
    public function createCustomToken($uid, array $claims = [], ?\DateTimeInterface $expiresAt = null): Token
    {
        $now = \time();
        $expiration = $expiresAt ? $expiresAt->getTimestamp() : $now + (60 * 60);

        $builder = (new Builder())
            ->withHeader('alg', 'RS256')
            ->withClaim('uid', (string) $uid)
            ->issuedBy($this->clientEmail)
            ->relatedTo($this->clientEmail)
            ->issuedAt($now)
            ->expiresAt($expiration)
            ->permittedFor('https://identitytoolkit.googleapis.com/google.identity.identitytoolkit.v1.IdentityToolkit');

        if (!empty($claims)) {
            $builder = $builder->withClaim('claims', $claims);
        }

        $token = $builder->getToken();

        $url = 'https://iam.googleapis.com/v1/projects/-/serviceAccounts/'.$this->clientEmail.':signBlob';

        try {
            $response = $this->client->request('POST', $url, [
                'json' => [
                    'bytesToSign' => \base64_encode($token->getPayload()),
                ],
            ]);
        } catch (Throwable $e) {
            throw (new AuthApiExceptionConverter())->convertException($e);
        }

        $result = JSON::decode((string) $response->getBody(), true);

        if ($base64EncodedSignature = $result['signature'] ?? null) {
            try {
                return (new Parser())->parse(((string) $token).$base64EncodedSignature);
            } catch (InvalidArgumentException $e) {
                throw new AuthError('The custom token API returned an unexpected value: '.$e->getMessage(), $e->getCode(), $e);
            }
        }

        throw new AuthError('Unable to create custom token.');
    }
}
