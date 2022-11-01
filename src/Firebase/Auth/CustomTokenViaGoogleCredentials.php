<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use Google\Auth\SignBlobInterface;
use Kreait\Firebase\Exception\Auth\AuthError;
use Kreait\Firebase\Util\DT;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Token\Parser;
use Stringable;

/**
 * @internal
 */
final class CustomTokenViaGoogleCredentials
{
    private SignBlobInterface $signer;
    private ?string $tenantId;
    private JoseEncoder $encoder;
    private Parser $parser;

    public function __construct(SignBlobInterface $signer, ?string $tenantId = null)
    {
        $this->signer = $signer;
        $this->tenantId = $tenantId;
        $this->encoder = new JoseEncoder();
        $this->parser = new Parser($this->encoder);
    }

    /**
     * @param Stringable|string $uid
     * @param array<string, mixed> $claims
     *
     * @throws AuthError
     */
    public function createCustomToken($uid, array $claims = [], ?DateTimeInterface $expiresAt = null): Token
    {
        $now = new DateTimeImmutable();
        $expiresAt = ($expiresAt !== null)
            ? DT::toUTCDateTimeImmutable($expiresAt)
            : $now->add(new DateInterval('PT1H'));

        $header = ['typ' => 'JWT', 'alg' => 'RS256'];
        $payload = [
            'iss' => $this->signer->getClientName(),
            'sub' => $this->signer->getClientName(),
            'aud' => 'https://identitytoolkit.googleapis.com/google.identity.identitytoolkit.v1.IdentityToolkit',
            'iat' => $now->getTimestamp(),
            'exp' => $expiresAt->getTimestamp(),
            'uid' => (string) $uid,
        ];

        if ($this->tenantId !== null) {
            $payload['tenant_id'] = $this->tenantId;
        }

        if ($claims !== []) {
            $payload['claims'] = $claims;
        }

        $base64UrlHeader = $this->base64EncodeArray($header);
        $base64UrlPayload = $this->base64EncodeArray($payload);

        $signature = $this->signer->signBlob($base64UrlHeader.'.'.$base64UrlPayload);
        $signature = str_replace(['=', '+', '/'], ['', '-', '_'], $signature);

        return $this->parser->parse(sprintf('%s.%s.%s', $base64UrlHeader, $base64UrlPayload, $signature));
    }

    /**
     * @param array<mixed> $array
     */
    private function base64EncodeArray(array $array): string
    {
        return $this->encoder->base64UrlEncode($this->encoder->jsonEncode($array));
    }
}
