<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\PromiseInterface;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingApiExceptionConverter;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Messaging\Http\Request\SendMessage;
use Kreait\Firebase\Messaging\Http\Request\ValidateMessage;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * @internal
 */
class ApiClient
{
    /** @var ClientInterface */
    private $client;

    /** @var MessagingApiExceptionConverter */
    private $errorHandler;

    /** @var string */
    private $projectId;

    /**
     * @internal
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
        $this->errorHandler = new MessagingApiExceptionConverter();

        // Extract the project ID from the client config (this will be refactored later)
        $baseUri = (string) $client->getConfig('base_uri');
        $uriParts = \explode('/', $baseUri);
        $this->projectId = (string) \array_pop($uriParts);
    }

    /**
     * @internal
     *
     * @deprecated 4.29.0
     */
    public function getClient(): ClientInterface
    {
        return $this->client;
    }

    /**
     * @deprecated 4.29.0
     */
    public function sendMessage(Message $message): ResponseInterface
    {
        return $this->send(new SendMessage($this->projectId, $message));
    }

    /**
     * @deprecated 4.29.0
     */
    public function sendMessageAsync(Message $message): PromiseInterface
    {
        return $this->sendAsync(new SendMessage($this->projectId, $message));
    }

    /**
     * @deprecated 4.29.0
     */
    public function validateMessage(Message $message): ResponseInterface
    {
        return $this->send(new ValidateMessage($this->projectId, $message));
    }

    /**
     * @deprecated 4.29.0
     */
    public function validateMessageAsync(Message $message): PromiseInterface
    {
        return $this->sendAsync(new ValidateMessage($this->projectId, $message));
    }

    /**
     * @internal
     *
     * @throws MessagingException
     * @throws FirebaseException
     */
    public function send(RequestInterface $request): ResponseInterface
    {
        try {
            return $this->client->send($request);
        } catch (Throwable $e) {
            throw $this->errorHandler->convertException($e);
        }
    }

    private function sendAsync(RequestInterface $request): PromiseInterface
    {
        return $this->client->sendAsync($request)
            ->then(null, function (Throwable $e) {
                throw $this->errorHandler->convertException($e);
            });
    }
}
