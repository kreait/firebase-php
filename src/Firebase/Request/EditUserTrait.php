<?php

declare(strict_types=1);

namespace Kreait\Firebase\Request;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Value\ClearTextPassword;
use Kreait\Firebase\Value\Email;
use Kreait\Firebase\Value\Uid;
use Kreait\Firebase\Value\Url;
use Stringable;

use function array_filter;
use function mb_strtolower;
use function preg_replace;

/**
 * @template T
 */
trait EditUserTrait
{
    protected ?string $uid = null;
    protected ?string $email = null;
    protected ?string $displayName = null;
    protected ?bool $emailIsVerified = null;
    protected ?string $phoneNumber = null;
    protected ?string $photoUrl = null;
    protected ?bool $markAsEnabled = null;
    protected ?bool $markAsDisabled = null;
    protected ?string $clearTextPassword = null;

    /**
     * @param Stringable|mixed $uid
     */
    public function withUid($uid): self
    {
        $request = clone $this;
        $request->uid = Uid::fromString($uid)->value;

        return $request;
    }

    public function withEmail(Stringable|string $email): self
    {
        $request = clone $this;
        $request->email = Email::fromString((string) $email)->value;

        return $request;
    }

    public function withVerifiedEmail(Stringable|string $email): self
    {
        $request = clone $this;
        $request->email = Email::fromString((string) $email)->value;
        $request->emailIsVerified = true;

        return $request;
    }

    public function withUnverifiedEmail(Stringable|string $email): self
    {
        $request = clone $this;
        $request->email = Email::fromString((string) $email)->value;
        $request->emailIsVerified = false;

        return $request;
    }

    public function withDisplayName(string $displayName): self
    {
        $request = clone $this;
        $request->displayName = $displayName;

        return $request;
    }

    /**
     * @param Stringable|string|null $phoneNumber
     */
    public function withPhoneNumber($phoneNumber): self
    {
        $phoneNumber = $phoneNumber !== null ? (string) $phoneNumber : null;

        $request = clone $this;
        $request->phoneNumber = $phoneNumber;

        return $request;
    }

    public function withPhotoUrl(Stringable|string $url): self
    {
        $request = clone $this;
        $request->photoUrl = Url::fromString($url)->value;

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

    public function withClearTextPassword(Stringable|string $clearTextPassword): self
    {
        $request = clone $this;
        $request->clearTextPassword = ClearTextPassword::fromString($clearTextPassword)->value;

        return $request;
    }

    /**
     * @return array<string, mixed>
     */
    public function prepareJsonSerialize(): array
    {
        $disableUser = null;

        if ($this->markAsDisabled) {
            $disableUser = true;
        } elseif ($this->markAsEnabled) {
            $disableUser = false;
        }

        return array_filter([
            'localId' => $this->uid,
            'disableUser' => $disableUser,
            'displayName' => $this->displayName,
            'email' => $this->email,
            'emailVerified' => $this->emailIsVerified,
            'phoneNumber' => $this->phoneNumber,
            'photoUrl' => $this->photoUrl,
            'password' => $this->clearTextPassword,
        ], static fn($value) => $value !== null);
    }

    public function hasUid(): bool
    {
        return (bool) $this->uid;
    }

    /**
     * @param T $request
     * @param array<string, mixed> $properties
     *
     * @throws InvalidArgumentException when invalid properties have been provided
     *
     * @return T
     */
    protected static function withEditableProperties(self $request, array $properties): self
    {
        foreach ($properties as $key => $value) {
            switch (mb_strtolower((string) preg_replace('/[^a-z]/i', '', $key))) {
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
                    if ($value === true) {
                        $request = $request->markEmailAsVerified();
                    } elseif ($value === false) {
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
                case 'disabled':
                case 'isdisabled':
                    if ($value === true) {
                        $request = $request->markAsDisabled();
                    } elseif ($value === false) {
                        $request = $request->markAsEnabled();
                    }

                    break;

                case 'enableuser':
                case 'enabled':
                case 'isenabled':
                    if ($value === true) {
                        $request = $request->markAsEnabled();
                    } elseif ($value === false) {
                        $request = $request->markAsDisabled();
                    }

                    break;

                case 'password':
                case 'cleartextpassword':
                    $request = $request->withClearTextPassword($value);

                    break;
            }
        }

        return $request;
    }
}
