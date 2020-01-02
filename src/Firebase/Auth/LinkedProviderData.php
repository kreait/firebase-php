<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use Kreait\Firebase\Value\Provider;

/**
 * @deprecated 4.41
 * @see \Kreait\Firebase\Auth::signInWithIdpAccessToken()
 * @see \Kreait\Firebase\Auth::signInWithIdpIdToken()
 */
class LinkedProviderData implements \JsonSerializable
{
    /**
     * @var UserRecord
     */
    public $userRecord;

    /**
     * @var string
     */
    public $federatedId;

    /**
     * @var Provider
     */
    public $provider;

    /**
     * The Firebase ID token.
     *
     * @var string
     */
    public $idToken;

    /**
     * @var string|null
     */
    public $refreshToken;

    /**
     * The provider's ID token (e.g. Google ID token).
     *
     * @var string|null
     */
    public $oauthIdToken;

    /**
     * The provider's Access token (e.g. Facebook Access token).
     *
     * @var string|null
     */
    public $oauthAccessToken;

    /**
     * @var array
     */
    public $rawUserInfo;

    public function __construct(UserRecord $userRecord)
    {
        $this->userRecord = $userRecord;
    }

    public static function fromResponseData(UserRecord $userRecord, array $data): self
    {
        $providerData = new self($userRecord);

        $providerData->federatedId = $data['federatedId'];
        $providerData->provider = new Provider($data['providerId']);
        $providerData->idToken = $data['idToken'];
        $providerData->refreshToken = $data['refreshToken'];
        $providerData->oauthIdToken = $data['oauthIdToken'] ?? null;
        $providerData->oauthAccessToken = $data['oauthAccessToken'] ?? null;
        $providerData->rawUserInfo = \json_decode($data['rawUserInfo'], true);

        return $providerData;
    }

    public function jsonSerialize()
    {
        return \get_object_vars($this);
    }
}
