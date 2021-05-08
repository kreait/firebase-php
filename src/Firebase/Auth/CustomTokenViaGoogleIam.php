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
use Kreait\Firebase\Util\DT;
use Kreait\Firebase\Util\JSON;
use Kreait\Firebase\Value\Uid;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token;
use Throwable;

class CustomTokenViaGoogleIam implements Generator
{
    private string $clientEmail;

    private ClientInterface $client;

    private Configuration $config;

    private ?TenantId $tenantId;

    public function __construct(string $clientEmail, ClientInterface $client, ?TenantId $tenantId = null)
    {
        $this->clientEmail = $clientEmail;
        $this->client = $client;
        $this->tenantId = $tenantId;

        $this->config = Configuration::forUnsecuredSigner();
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
        $expiresAt = ($expiresAt !== null)
            ? DT::toUTCDateTimeImmutable($expiresAt)
            : $now->add(new \DateInterval('PT1H'));

        $builder = $this->config->builder()
            ->withClaim('uid', (string) $uid)
            ->issuedBy($this->clientEmail)
            ->permittedFor('https://identitytoolkit.googleapis.com/google.identity.identitytoolkit.v1.IdentityToolkit')
            ->relatedTo($this->clientEmail)
            ->issuedAt($now)
            ->expiresAt($expiresAt)
        ;

        if ($this->tenantId) {
            $builder->withClaim('tenantId', $this->tenantId->toString());
        }

        if (!empty($claims)) {
            $builder->withClaim('claims', $claims);
        }

        $token = $builder->getToken($this->config->signer(), $this->config->signingKey());

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
                return $this->config->parser()->parse($token->payload().'.'.$base64EncodedSignature);
            } catch (InvalidArgumentException $e) {
                throw new AuthError('The custom token API returned an unexpected value: '.$e->getMessage(), $e->getCode(), $e);
            }
        }

        throw new AuthError('Unable to create custom token.');
    }
}
