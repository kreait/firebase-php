<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database;

use Kreait\Firebase\Database\ApiClient;
use Kreait\Firebase\Database\Reference;
use Kreait\Firebase\Database\Transaction;
use Kreait\Firebase\Exception\Database\ReferenceHasNotBeenSnapshotted;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class TransactionTest extends TestCase
{
    /** @var Transaction */
    private $transaction;

    protected function setUp()
    {
        $this->transaction = new Transaction($this->createMock(ApiClient::class));
    }

    public function testAReferenceCanNotBeChangedIfItHasNotBeenSnapshotted()
    {
        $reference = $this->createMock(Reference::class);

        $this->expectException(ReferenceHasNotBeenSnapshotted::class);
        $this->transaction->set($reference, 'does not matter');
    }
}
