<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Auth;

use Kreait\Firebase\Auth\DeleteUsersRequest;
use Kreait\Firebase\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function array_map;
use function range;

/**
 * @internal
 */
final class DeleteUsersRequestTest extends TestCase
{
    #[Test]
    public function itRejectsTooManyUids(): void
    {
        $uids = array_map('strval', range(0, 1001));

        $this->expectException(InvalidArgumentException::class);
        DeleteUsersRequest::withUids($uids);
    }

    #[Test]
    public function itRejectsInvalidUids(): void
    {
        $this->expectException(InvalidArgumentException::class);
        DeleteUsersRequest::withUids(['']);
    }
}
