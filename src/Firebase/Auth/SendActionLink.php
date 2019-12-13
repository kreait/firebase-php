<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use Kreait\Firebase\Value\Email;

final class SendActionLink
{
    /** @var CreateActionLink */
    private $action;

    /** @var string|null */
    private $locale;

    /** @var string|null */
    private $idTokenString;

    public function __construct(CreateActionLink $action, string $locale = null)
    {
        $this->action = $action;
        $this->locale = $locale;
    }

    public function type(): string
    {
        return $this->action->type();
    }

    public function email(): Email
    {
        return $this->action->email();
    }

    public function settings(): ActionCodeSettings
    {
        return $this->action->settings();
    }

    /**
     * @return string|null
     */
    public function locale()
    {
        return $this->locale;
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
     *
     * @return string|null
     */
    public function idTokenString()
    {
        return $this->idTokenString;
    }
}
