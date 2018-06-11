<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

abstract class Message implements \JsonSerializable
{
    use MessageTrait;
}
