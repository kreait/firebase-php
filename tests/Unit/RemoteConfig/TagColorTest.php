<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\RemoteConfig;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\RemoteConfig\TagColor;
use Kreait\Firebase\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
final class TagColorTest extends UnitTestCase
{
    #[Test]
    public function createWithInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new TagColor('foo');
    }
}
