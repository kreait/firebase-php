<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Messaging\MessageTarget;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class MessageTargetTest extends TestCase
{
    public function testInvalidTargetCausesError()
    {
        $this->expectException(InvalidArgumentException::class);
        MessageTarget::with('foo', 'bar');
    }
}
