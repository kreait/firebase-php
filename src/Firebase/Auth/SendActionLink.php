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
}
