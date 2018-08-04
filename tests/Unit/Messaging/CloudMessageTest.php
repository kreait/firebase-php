<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Messaging\CloudMessage;
use PHPUnit\Framework\TestCase;

class CloudMessageTest extends TestCase
{
    public function testInvalidTargetCausesError()
    {
        $this->expectException(InvalidArgumentException::class);
        CloudMessage::withTarget('foo', 'bar');
    }
}
