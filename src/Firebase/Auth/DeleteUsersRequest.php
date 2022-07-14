<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Value\Uid;

final class DeleteUsersRequest
{
    private const MAX_BATCH_SIZE = 1000;

    /** @var string[] */
    private array $uids;
    private bool $enabledUsersShouldBeForceDeleted;

    /**
     * @param string[] $uids
     */
    private function __construct(array $uids, bool $enabledUsersShouldBeForceDeleted)
    {
        $this->uids = $uids;
        $this->enabledUsersShouldBeForceDeleted = $enabledUsersShouldBeForceDeleted;
    }

    /**
     * @param iterable<\Stringable|string> $uids
     */
    public static function withUids(iterable $uids, bool $forceDeleteEnabledUsers = false): self
    {
        $validatedUids = [];
        $count = 0;

        foreach ($uids as $uid) {
            $validatedUids[] = (string) (new Uid((string) $uid));
            ++$count;

            if ($count > self::MAX_BATCH_SIZE) {
                throw new InvalidArgumentException('Only '.self::MAX_BATCH_SIZE.' users can be deleted at a time');
            }
        }

        return new self($validatedUids, $forceDeleteEnabledUsers);
    }

    /**
     * @return string[]
     */
    public function uids(): array
    {
        return $this->uids;
    }

    public function enabledUsersShouldBeForceDeleted(): bool
    {
        return $this->enabledUsersShouldBeForceDeleted;
    }
}
