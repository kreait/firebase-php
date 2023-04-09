<?php

declare(strict_types=1);

namespace Kreait\Firebase\Database;

use Kreait\Firebase\Exception\Database\ReferenceHasNotBeenSnapshotted;
use Kreait\Firebase\Exception\Database\TransactionFailed;
use Kreait\Firebase\Exception\DatabaseException;

use function array_key_exists;

class Transaction
{
    /**
     * @var array<string, string>
     */
    private array $etags;

    /**
     * @internal
     */
    public function __construct(private readonly ApiClient $apiClient)
    {
        $this->etags = [];
    }

    /**
     * @throws DatabaseException
     */
    public function snapshot(Reference $reference): Snapshot
    {
        $path = $reference->getPath();

        $result = $this->apiClient->getWithETag($path);

        $this->etags[$path] = $result['etag'];

        return new Snapshot($reference, $result['value']);
    }

    /**
     * @throws ReferenceHasNotBeenSnapshotted
     * @throws TransactionFailed
     */
    public function set(Reference $reference, mixed $value): void
    {
        $etag = $this->getEtagForReference($reference);

        try {
            $this->apiClient->setWithEtag($reference->getPath(), $value, $etag);
        } catch (DatabaseException $e) {
            throw TransactionFailed::onReference($reference, $e);
        }
    }

    /**
     * @throws ReferenceHasNotBeenSnapshotted
     * @throws TransactionFailed
     */
    public function remove(Reference $reference): void
    {
        $etag = $this->getEtagForReference($reference);

        try {
            $this->apiClient->removeWithEtag($reference->getPath(), $etag);
        } catch (DatabaseException $e) {
            throw TransactionFailed::onReference($reference, $e);
        }
    }

    /**
     * @throws ReferenceHasNotBeenSnapshotted
     */
    private function getEtagForReference(Reference $reference): string
    {
        $path = $reference->getPath();

        if (array_key_exists($path, $this->etags)) {
            return $this->etags[$path];
        }

        throw new ReferenceHasNotBeenSnapshotted($reference);
    }
}
