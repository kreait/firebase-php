<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use DateTimeImmutable;

interface Clock
{
    public function now(): DateTimeImmutable;
}
