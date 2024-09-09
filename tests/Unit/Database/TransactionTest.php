<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database;

use Kreait\Firebase\Database\ApiClient;
use Kreait\Firebase\Database\Reference;
use Kreait\Firebase\Database\Transaction;
use Kreait\Firebase\Exception\Database\DatabaseError;
use Kreait\Firebase\Exception\Database\ReferenceHasNotBeenSnapshotted;
use Kreait\Firebase\Exception\Database\TransactionFailed;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * @internal
 */
final class TransactionTest extends TestCase
{
    private ApiClient&MockObject $apiClient;
    private Transaction $transaction;

    protected function setUp(): void
    {
        $this->apiClient = $this->createMock(ApiClient::class);

        $this->transaction = new Transaction($this->apiClient);
    }

    #[Test]
    public function aReferenceCanOnlyBeChangedIfItHasBeenSnapshotted(): void
    {
        $reference = $this->createMock(Reference::class);

        try {
            $this->transaction->set($reference, 'does not matter');
        } catch (ReferenceHasNotBeenSnapshotted $e) {
            $this->assertSame($reference, $e->getReference());
        } catch (Throwable) {
            $this->fail('A '.ReferenceHasNotBeenSnapshotted::class.' should have been thrown');
        }
    }

    #[Test]
    public function aTransactionCanFail(): void
    {
        $reference = $this->createMock(Reference::class);
        $reference->method('getPath')->willReturn('/foo');

        $this->apiClient
            ->method('getWithETag')
            ->with('/foo')
            ->willReturn(['etag' => 'etag', 'value' => 'old value'])
        ;

        $this->apiClient
            ->method('setWithEtag')
            ->with('/foo')
            ->willThrowException(new DatabaseError())
        ;

        $this->transaction->snapshot($reference);

        $this->expectException(TransactionFailed::class);
        $this->transaction->set($reference, 'new value');
    }
}
