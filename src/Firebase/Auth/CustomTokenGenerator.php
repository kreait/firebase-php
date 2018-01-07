<?php

namespace Kreait\Firebase\Auth;

use Kreait\Firebase\ServiceAccount;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;

class CustomTokenGenerator
{
    /**
     * @var Builder
     */
    private $builder;

    /**
     * @var Signer
     */
    private $signer;

    /**
     * @var ServiceAccount
     */
    private $serviceAccount;

    public function __construct(ServiceAccount $serviceAccount, Builder $builder = null, Signer $signer = null)
    {
        $this->serviceAccount = $serviceAccount;
        $this->signer = $signer ?? new Sha256();
        $this->builder = $builder ?? $this->createBuilder();
    }

    /**
     * Returns a token for the given user and claims.
     *
     * @param mixed $uid
     * @param array $claims
     * @param \DateTimeInterface $expiresAt
     *
     * @throws \BadMethodCallException when a claim is invalid
     *
     * @return Token
     */
    public function create($uid, array $claims = [], \DateTimeInterface $expiresAt = null): Token
    {
        if (\count($claims)) {
            $this->builder->set('claims', $claims);
        }

        $this->builder->set('uid', (string) $uid);

        $now = time();
        $expiration = $expiresAt ? $expiresAt->getTimestamp() : $now + (60 * 60);

        $token = $this->builder
            ->setIssuedAt($now)
            ->setExpiration($expiration)
            ->sign($this->signer, $this->serviceAccount->getPrivateKey())
            ->getToken();

        $this->builder->unsign();

        return $token;
    }

    private function createBuilder(): Builder
    {
        return (new Builder())
                ->setIssuer($this->serviceAccount->getClientEmail())
                ->setSubject($this->serviceAccount->getClientEmail())
                ->setAudience('https://identitytoolkit.googleapis.com/google.identity.identitytoolkit.v1.IdentityToolkit');
    }
}
