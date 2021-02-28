<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use Google\Cloud\Firestore\FirestoreClient;

final class Firestore implements Contract\Firestore
{
    /** @var FirestoreClient */
    private $client;

    private function __construct()
    {
    }

    public static function withFirestoreClient(FirestoreClient $firestoreClient): self
    {
        $firestore = new self();
        $firestore->client = $firestoreClient;

        return $firestore;
    }

    public function database(): FirestoreClient
    {
        return $this->client;
    }
}
