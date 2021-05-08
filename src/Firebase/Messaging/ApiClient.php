<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\PromiseInterface;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingApiExceptionConverter;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Http\WrappedGuzzleClient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * @internal
 */
class ApiClient implements ClientInterface
{
    use WrappedGuzzleClient;

    private MessagingApiExceptionConverter $errorHandler;

    /**
     * @internal
     */
    public function __construct(ClientInterface $client, MessagingApiExceptionConverter $errorHandler)
    {
        $this->client = $client;
        $this->errorHandler = $errorHandler;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @throws MessagingException
     * @throws FirebaseException
     */
    public function send(RequestInterface $request, array $options = []): ResponseInterface
    {
        try {
            return $this->client->send($request);
        } catch (Throwable $e) {
            throw $this->errorHandler->convertException($e);
        }
    }

    /**
     * @param array<string, mixed> $options
     */
    public function sendAsync(RequestInterface $request, array $options = []): PromiseInterface
    {
        return $this->client->sendAsync($request, $options)
            ->then(null, function (Throwable $e): void {
                throw $this->errorHandler->convertException($e);
            })
        ;
    }
}
