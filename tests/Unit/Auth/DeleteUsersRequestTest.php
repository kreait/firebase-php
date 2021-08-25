<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Auth;

use Kreait\Firebase\Auth\DeleteUsersRequest;
use Kreait\Firebase\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class DeleteUsersRequestTest extends TestCase
{
    public function testItRejectsTooManyUids(): void
    {
        $uids = \array_map('strval', \range(0, 1001));

        $this->expectException(InvalidArgumentException::class);
        DeleteUsersRequest::withUids('project-id', $uids);
    }

    public function testItRejectsInvalidUids(): void
    {
        $this->expectException(InvalidArgumentException::class);
        DeleteUsersRequest::withUids('project-id', ['']);
    }
}
