<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

/**
 * @internal
 */
final class SendActionLink
{
    private ?string $idTokenString = null;

    public function __construct(private CreateActionLink $action, private readonly ?string $locale = null)
    {
    }

    public function type(): string
    {
        return $this->action->type();
    }

    public function email(): string
    {
        return $this->action->email();
    }

    public function settings(): ActionCodeSettings
    {
        return $this->action->settings();
    }

    public function tenantId(): ?string
    {
        return $this->action->tenantId();
    }

    public function locale(): ?string
    {
        return $this->locale;
    }

    /**
     * @internal
     *
     * Only to be used when the API endpoint expects the ID Token of the given user.
     *
     * Currently, this seems only to be the case on VERIFY_EMAIL actions.
     *
     * @see https://github.com/firebase/firebase-js-sdk/issues/1958
     */
    public function withIdTokenString(string $idTokenString): self
    {
        $instance = clone $this;
        $instance->action = clone $this->action;
        $instance->idTokenString = $idTokenString;

        return $instance;
    }

    /**
     * @internal
     *
     * Only to be used when the API endpoint expects the ID Token of the given user.
     *
     * Currently seems only to be the case on VERIFY_EMAIL actions.
     *
     * @see https://github.com/firebase/firebase-js-sdk/issues/1958
     */
    public function idTokenString(): ?string
    {
        return $this->idTokenString;
    }
}
