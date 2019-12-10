<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use Kreait\Firebase\Value\Email;

final class SendActionLink
{
    /** @var CreateActionLink */
    private $action;

    public function __construct(CreateActionLink $action)
    {
        $this->action = $action;
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
}
