<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use DateTimeImmutable;
use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Util\JSON;
use Kreait\Firebase\Value\Email;
use Kreait\Firebase\Value\PhoneNumber;
use Kreait\Firebase\Value\Uid;
use Kreait\Firebase\Value\Url;

class ImportUserRecord implements \JsonSerializable
{
    private ?Uid $uid = null;
    private ?Email $email = null;
    private ?bool $emailVerified = null;
    private ?string $displayName = null;
    private ?Url $photoUrl = null;
    private ?string $phoneNumber = null;

    /** @var array<string, mixed> */
    private array $customClaims = [];

    private ?DateTimeImmutable $tokensValidAfterTime = null;

    /** @var bool|null */
    private ?bool $markAsEnabled = null;
    private ?bool $markAsDisabled = null;

    /** @var list<UserInfo> */
    private array $providers = [];

    private function __construct()
    {
    }

    public static function new(): self
    {
        return new self();
    }

    public function withUid(string $uid): self
    {
        $request = clone $this;
        $request->uid = new Uid($uid);

        return $request;
    }

    /**
     * @return static
     */
    public function withEmail(string $email): self
    {
        $request = clone $this;
        $request->email = new Email($email);

        return $request;
    }

    public function withVerifiedEmail(string $email): self
    {
        $request = clone $this;
        $request->email = new Email($email);
        $request->emailVerified = true;

        return $request;
    }

    public function withUnverifiedEmail(string $email): self
    {
        $request = clone $this;
        $request->email = new Email($email);
        $request->emailVerified = false;

        return $request;
    }

    public function withDisplayName(string $displayName): self
    {
        $request = clone $this;
        $request->displayName = $displayName;

        return $request;
    }

    public function withPhotoUrl(string $url): self
    {
        $request = clone $this;
        $request->photoUrl = new Url(new Uri($url));

        return $request;
    }

    public function withPhoneNumber(string $phoneNumber): self
    {
        $request = clone $this;
        $request->phoneNumber = $phoneNumber;

        return $request;
    }

    /**
     * @param array<string, mixed> $claims
     */
    public function withCustomClaims(array $claims): self
    {
        $request = clone $this;
        $request->customClaims = $claims;

        return $request;
    }

    public function markTokensValidAfter(DateTimeImmutable $after): self
    {
        $request = clone $this;
        $request->tokensValidAfterTime = $after;

        return $request;
    }

    /**
     * @return static
     */
    public function markAsDisabled(): self
    {
        $request = clone $this;
        $request->markAsEnabled = null;
        $request->markAsDisabled = true;

        return $request;
    }

    /**
     * @return static
     */
    public function markAsEnabled(): self
    {
        $request = clone $this;
        $request->markAsDisabled = null;
        $request->markAsEnabled = true;

        return $request;
    }

    /**
     * @param array<UserInfo> $providers
     *
     * @return $this
     */
    public function withProviders(array $providers): self
    {
        $request = clone $this;
        $request->providers = $providers;

        return $request;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        if ($this->uid === null) {
            throw new InvalidArgumentException('A uid is required to import user.');
        }

        $disableUser = null;

        if ($this->markAsDisabled) {
            $disableUser = true;
        } elseif ($this->markAsEnabled) {
            $disableUser = false;
        }

        $customClaims = \count($this->customClaims) > 0 ? Json::encode($this->customClaims) : null;
        $tokensValidAfterTime = $this->tokensValidAfterTime !== null
            ? $this->tokensValidAfterTime->format(\DATE_ATOM)
            : null;

        $record = [
            'localId' => $this->uid,
            'email' => $this->email,
            'emailVerified' => $this->emailVerified,
            'displayName' => $this->displayName,
            'disabled' => $disableUser,
            'phoneNumber' => $this->phoneNumber,
            'photoUrl' => $this->photoUrl,
            'customAttributes' => $customClaims,
            'validSince' => $tokensValidAfterTime,
        ];

        if (\count($this->providers) > 0) {
            foreach ($this->providers as $providerData) {
                $record['providerUserInfo'][] = $providerData->jsonSerialize();
            }
        }

        return \array_filter(
            $record,
            static function ($value) {
                return $value !== null;
            }
        );
    }
}
