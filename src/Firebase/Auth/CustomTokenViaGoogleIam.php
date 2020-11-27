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
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Parsing\Encoder;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Token\DataSet;
use Throwable;

class CustomTokenViaGoogleIam implements Generator
{
    /** @var string */
    private $clientEmail;

    /** @var ClientInterface */
    private $client;

    /** @var Encoder */
    private $encoder;

    public function __construct(string $clientEmail, ClientInterface $client)
    {
        $this->clientEmail = $clientEmail;
        $this->client = $client;
        $this->encoder = new Encoder();
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
        $now = new \DateTimeImmutable();
        $expiresAt = $expiresAt ?: $now->add(new \DateInterval('PT1H'));

        $headers = ['typ' => 'JWT', 'alg' => 'none'];
        $headers = new DataSet($headers, $this->encoder->base64UrlEncode($this->encoder->jsonEncode($headers)));

        $jwtClaims = [
            'uid' => (string) $uid,
            'iss' => $this->clientEmail,
            'sub' => $this->clientEmail,
            'iat' => $now->getTimestamp(),
            'exp' => $expiresAt->getTimestamp(),
            'aud' => 'https://identitytoolkit.googleapis.com/google.identity.identitytoolkit.v1.IdentityToolkit',
        ];

        if (!empty($claims)) {
            $jwtClaims['claims'] = $claims;
        }

        $jwtClaims = new DataSet($jwtClaims, $this->encoder->base64UrlEncode($this->encoder->jsonEncode($jwtClaims)));

        $token = new Token($headers, $jwtClaims);

        $url = 'https://iam.googleapis.com/v1/projects/-/serviceAccounts/'.$this->clientEmail.':signBlob';

        try {
            $response = $this->client->request('POST', $url, [
                'json' => [
                    'bytesToSign' => \base64_encode($token->payload()),
                ],
            ]);
        } catch (Throwable $e) {
            throw (new AuthApiExceptionConverter())->convertException($e);
        }

        $result = JSON::decode((string) $response->getBody(), true);

        if ($base64EncodedSignature = $result['signature'] ?? null) {
            try {
                return (new Parser())->parse($token->payload().'.'.$base64EncodedSignature);
            } catch (InvalidArgumentException $e) {
                throw new AuthError('The custom token API returned an unexpected value: '.$e->getMessage(), $e->getCode(), $e);
            }
        }

        throw new AuthError('Unable to create custom token.');
    }
}
