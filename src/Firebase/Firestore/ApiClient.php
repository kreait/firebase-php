<?php

declare(strict_types=1);

namespace Kreait\Firebase\Firestore;

use Beste\Json;
use GuzzleHttp\ClientInterface;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\FirestoreApiExceptionConverter;
use Kreait\Firebase\Exception\FirestoreException;
use Throwable;

/**
 * @internal
 */
class ApiClient
{
    private ClientInterface $client;
    private FirestoreApiExceptionConverter $errorHandler;

    /**
     * @internal
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
        $this->errorHandler = new FirestoreApiExceptionConverter();
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return mixed
     * @throws FirebaseException
     * @throws FirestoreException
     */
    public function get(string $path, array $options = [])
    {
        return $this->requestApi('GET', $path, $options);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $options
     *
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
     * @param array<string, mixed> $options
     *
     * @throws FirestoreException
     * @throws FirebaseException
     * @return mixed
     */
    private function requestApi(string $method, string $uri, array $options = [])
    {
        try {
            $response = $this->client->request($method, $uri, $options);

            return Json::decode((string) $response->getBody());
        } catch (Throwable $e) {
            throw $this->errorHandler->convertException($e);
        }
    }
}
