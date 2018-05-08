<?php

declare(strict_types=1);

namespace Kreait\Firebase\Request;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Value\ClearTextPassword;
use Kreait\Firebase\Value\Email;
use Kreait\Firebase\Value\PhoneNumber;
use Kreait\Firebase\Value\Uid;
use Kreait\Firebase\Value\Url;

trait EditUserTrait
{
    /**
     * @var Uid|null
     */
    protected $uid;

    /**
     * @var Email|null
     */
    protected $email;

    /**
     * @var string|null
     */
    protected $displayName;

    /**
     * @var bool|null
     */
    protected $emailIsVerified;

    /**
     * @var PhoneNumber|null
     */
    protected $phoneNumber;

    /**
     * @var Url|null
     */
    protected $photoUrl;

    /**
     * @var bool|null
     */
    protected $markAsEnabled;

    /**
     * @var bool|null
     */
    protected $markAsDisabled;

    /**
     * @var ClearTextPassword|null
     */
    protected $clearTextPassword;

    /**
     * @param static $request
     * @param array $properties
     *
     * @throws InvalidArgumentException when invalid properties have been provided
     *
     * @return static
     */
    protected static function withEditableProperties($request, array $properties)
    {
        foreach ($properties as $key => $value) {
            switch (strtolower(preg_replace('/[^a-z]/i', '', $key))) {
                case 'uid':
                case 'localid':
                    $request = $request->withUid($value);
                    break;
                case 'email':
                    $request = $request->withEmail($value);
                    break;
                case 'unverifiedemail':
                    $request = $request->withUnverifiedEmail($value);
                    break;
                case 'verifiedemail':
                    $request = $request->withVerifiedEmail($value);
                    break;
                case 'emailverified':
                    if ($value) {
                        $request = $request->markEmailAsVerified();
                    } elseif (false === $value) {
                        $request = $request->markEmailAsUnverified();
                    }
                    break;
                case 'displayname':
                    $request = $request->withDisplayName($value);
                    break;
                case 'phone':
                case 'phonenumber':
                    $request = $request->withPhoneNumber($value);
                    break;
                case 'photo':
                case 'photourl':
                    $request = $request->withPhotoUrl($value);
                    break;
                case 'disableuser':
                    if ($value) {
                        $request = $request->markAsDisabled();
                    } else {
                        $request = $request->markAsEnabled();
                    }
                    break;
                case 'disabled':
                case 'isdisabled':
                    $request = $request->markAsDisabled();
                    break;
                case 'enabled':
                case 'isenabled':
                    $request = $request->markAsEnabled();
                    break;
                case 'password':
                case 'cleartextpassword':
                    $request = $request->withClearTextPassword($value);
                    break;
            }
        }

        return $request;
    }

    public function withUid($uid): self
    {
        $request = clone $this;
        $request->uid = $uid instanceof Uid ? $uid : new Uid($uid);

        return $request;
    }

    public function withEmail($email): self
    {
        $request = clone $this;
        $request->email = $email instanceof Email ? $email : new Email($email);

        return $request;
    }

    public function withVerifiedEmail($email): self
    {
        $request = clone $this;
        $request->email = $email instanceof Email ? $email : new Email($email);
        $request->emailIsVerified = true;

        return $request;
    }

    public function withUnverifiedEmail($email): self
    {
        $request = clone $this;
        $request->email = $email instanceof Email ? $email : new Email($email);
        $request->emailIsVerified = false;

        return $request;
    }

    public function withDisplayName(string $displayName): self
    {
        $request = clone $this;
        $request->displayName = $displayName;

        return $request;
    }

    public function withPhoneNumber($phoneNumber): self
    {
        if ($phoneNumber) {
            $phoneNumber = $phoneNumber instanceof PhoneNumber
                ? $phoneNumber
                : new PhoneNumber($phoneNumber)
            ;
        }
        $request = clone $this;
        $request->phoneNumber = $phoneNumber;

        return $request;
    }

    public function withPhotoUrl($url): self
    {
        $request = clone $this;
        $request->photoUrl = $url instanceof Url ? $url : Url::fromValue($url);

        return $request;
    }

    public function markAsDisabled(): self
    {
        $request = clone $this;
        $request->markAsEnabled = null;
        $request->markAsDisabled = true;

        return $request;
    }

    public function markAsEnabled(): self
    {
        $request = clone $this;
        $request->markAsDisabled = null;
        $request->markAsEnabled = true;

        return $request;
    }

    public function markEmailAsVerified(): self
    {
        $request = clone $this;
        $request->emailIsVerified = true;

        return $request;
    }

    public function markEmailAsUnverified(): self
    {
        $request = clone $this;
        $request->emailIsVerified = false;

        return $request;
    }

    public function withClearTextPassword($clearTextPassword): self
    {
        $request = clone $this;
        $request->clearTextPassword = $clearTextPassword instanceof ClearTextPassword
            ? $clearTextPassword
            : new ClearTextPassword($clearTextPassword)
        ;

        return $request;
    }

    public function prepareJsonSerialize(): array
    {
        $disableUser = null;
        if ($this->markAsDisabled) {
            $disableUser = true;
        } elseif ($this->markAsEnabled) {
            $disableUser = false;
        }

        $data = array_filter([
            'localId' => $this->uid,
            'disableUser' => $disableUser,
            'displayName' => $this->displayName,
            'email' => $this->email,
            'emailVerified' => $this->emailIsVerified,
            'phoneNumber' => $this->phoneNumber,
            'photoUrl' => $this->photoUrl,
            'password' => $this->clearTextPassword,
        ], function ($value) {
            return null !== $value;
        });

        return $data;
    }

    public function hasUid(): bool
    {
        return (bool) $this->uid;
    }
}
