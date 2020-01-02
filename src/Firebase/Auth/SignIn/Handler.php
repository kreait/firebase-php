<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth\SignIn;

use Kreait\Firebase\Auth\SignIn;
use Kreait\Firebase\Auth\SignInResult;
use Kreait\Firebase\Exception\InvalidArgumentException;

interface Handler
{
    /**
     * @throws InvalidArgumentException If the handler does not support this action
     * @throws FailedToSignIn
     */
    public function handle(SignIn $action): SignInResult;
}
