<?php

declare(strict_types=1);

namespace Kreait\Firebase\Database;

use Kreait\Firebase\Exception\ApiException;
use Kreait\Firebase\Exception\Database\ReferenceHasNotBeenSnapshotted;
use Kreait\Firebase\Exception\Database\TransactionFailed;

class Transaction
{
    /**
     * @var ApiClient
     */
    private $apiClient;

    /**
     * @var string[]
     */
    private $etags;

    /**
     * @internal
     */
    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
        $this->etags = [];
    }

    public function snapshot(Reference $reference): Snapshot
    {
        $uri = (string) $reference->getUri();

        $result = $this->apiClient->getWithETag($uri);

        $this->etags[$uri] = $result['etag'];

        return new Snapshot($reference, $result['value']);
    }

    /**
     * @param Reference $reference
     * @param mixed $value
     *
     * @throws ReferenceHasNotBeenSnapshotted
     * @throws TransactionFailed
     */
    public function set(Reference $reference, $value)
    {
        $etag = $this->getEtagForReference($reference);

        try {
            $this->apiClient->setWithEtag($reference->getUri(), $value, $etag);
        } catch (ApiException $e) {
            throw TransactionFailed::forReferenceAndApiException($reference, $e);
        }
    }

    /**
     * @throws ReferenceHasNotBeenSnapshotted
     * @throws TransactionFailed
     */
    public function remove(Reference $reference)
    {
        $etag = $this->getEtagForReference($reference);

        try {
            $this->apiClient->removeWithEtag($reference->getUri(), $etag);
        } catch (ApiException $e) {
            throw TransactionFailed::forReferenceAndApiException($reference, $e);
        }
    }

    /**
     * @throws ReferenceHasNotBeenSnapshotted
     */
    private function getEtagForReference(Reference $reference): string
    {
        $uri = (string) $reference->getUri();

        if (\array_key_exists($uri, $this->etags)) {
            return $this->etags[$uri];
        }

        throw ReferenceHasNotBeenSnapshotted::with($reference);
    }
}
