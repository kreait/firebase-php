<?php

declare(strict_types=1);

namespace Kreait\Firebase;

interface Value
{
    public function equalsTo($other): bool;
}
