<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Exception\Messaging;

use Kreait\Firebase\Exception\Messaging\NotFound;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class NotFoundTest extends TestCase
{
    #[Test]
    public function itProvidesTheToken(): void
    {
        $exception = NotFound::becauseTokenNotFound('token');

        $this->assertSame('token', $exception->token());
    }
}
