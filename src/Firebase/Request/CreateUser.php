<?php

declare(strict_types=1);

namespace Kreait\Firebase\Request;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Request;
use Kreait\Firebase\Value\ClearTextPassword;
use Kreait\Firebase\Value\Email;
use Kreait\Firebase\Value\PhoneNumber;
use Kreait\Firebase\Value\Uid;
use Kreait\Firebase\Value\Url;

final class CreateUser implements Request
{
    /**
     * @var Uid
     */
    private $uid;

    /**
     * @var Email
     */
    private $email;

    /**
     * @var string
     */
    private $displayName;

    /**
     * @var bool
     */
    private $emailIsVerified;

    /**
     * @var PhoneNumber
     */
    private $phoneNumber;

    /**
     * @var Url
     */
    private $photoUrl;

    /**
     * @var bool
     */
    private $isEnabled = true;

    /**
     * @var ClearTextPassword
     */
    private $clearTextPassword;

    private function __construct()
    {
    }

    public static function new(): self
    {
        return new self();
    }

    /**
     * @throws InvalidArgumentException when invalid properties have been provided
     */
    public static function withProperties(array $properties): self
    {
        $request = new self();

        foreach ($properties as $key => $value) {
            switch (strtolower($key)) {
                case 'uid':
                case 'localid':
                    $request = $request->withUid($value);
                    break;
                case 'email':
                case 'unverifiedemail':
                    $request = $request->withUnverifiedEmail($value);
                    break;
                case 'verifiedemail':
                    $request = $request->withVerifiedEmail($value);
                    break;
                case 'emailverified':
                    if ($value) {
                        $request = $request->markEmailAsVerified();
                    } else {
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
        $request = clone $this;
        $request->phoneNumber = $phoneNumber instanceof PhoneNumber
            ? $phoneNumber
            : new PhoneNumber($phoneNumber)
        ;

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
        $request->isEnabled = false;

        return $request;
    }

    public function markAsEnabled(): self
    {
        $request = clone $this;
        $request->isEnabled = true;

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

    public function jsonSerialize()
    {
        $data = array_filter([
            'localId' => $this->uid,
            'disableUser' => $this->isEnabled ? null : true,
            'displayName' => $this->displayName,
            'email' => $this->email,
            'emailVerified' => $this->emailIsVerified,
            'phoneNumber' => $this->phoneNumber,
            'photoUrl' => $this->photoUrl,
            'password' => $this->clearTextPassword,
        ], function ($value) {
            return null !== $value;
        });

        if (array_key_exists('emailVerified', $data) && !array_key_exists('email', $data)) {
            unset($data['emailVerified']);
        }

        return $data;
    }
}
