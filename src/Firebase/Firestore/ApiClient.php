<?php

declare(strict_types=1);

namespace Kreait\Firebase\Firestore;

use GuzzleHttp\ClientInterface;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\FirestoreApiExceptionConverter;
use Kreait\Firebase\Exception\FirestoreException;
use Kreait\Firebase\Util\JSON;
use Throwable;

/**
 * @internal
 */
class ApiClient
{
    private ClientInterface $client;

    /** @var FirestoreApiExceptionConverter */
    private $errorHandler;

    /**
     * @internal
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
        $this->errorHandler = new FirestoreApiExceptionConverter();
    }

    /**
     * @return mixed
     * @throws FirebaseException
     * @throws FirestoreException
     */
    public function get(string $path, array $options = [])
    {
        return $this->requestApi('GET', $path, $options);
    }

    /**
     * @return mixed
     * @throws FirebaseException
     * @throws FirestoreException
     */
    public function patch(string $path, array $data, array $options = [])
    {
        $options['json'] = $data;

        return $this->requestApi('PATCH', $path, $options);
    }

    /**
     * @throws FirestoreException
     * @throws FirebaseException
     * @return mixed
     */
    private function requestApi(string $method, string $uri, array $options = [])
    {
        try {
            $response = $this->client->request($method, $uri, $options);

            return JSON::decode((string) $response->getBody());
        } catch (Throwable $e) {
            throw $this->errorHandler->convertException($e);
        }
    }
}
