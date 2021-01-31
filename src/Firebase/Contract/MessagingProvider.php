<?php

declare(strict_types=1);

namespace Kreait\Firebase\Contract;

interface MessagingProvider
{
    public function messaging(): Messaging;
}
