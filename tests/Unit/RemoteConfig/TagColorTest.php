<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\RemoteConfig;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\RemoteConfig\TagColor;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
class TagColorTest extends UnitTestCase
{
    public function testCreateWithInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new TagColor('foo');
    }
}
