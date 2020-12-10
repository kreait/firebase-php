<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging\Http\Request;

use Kreait\Firebase\Messaging\Message;

interface MessageRequest
{
    public function message(): Message;
}
