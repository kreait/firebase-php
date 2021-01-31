<?php

declare(strict_types=1);

namespace Kreait\Firebase\Contract;

interface AuthProvider
{
    public function auth(): Auth;
}
