<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Auth;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Kreait\Firebase\Auth\SignIn;
use Kreait\Firebase\Auth\SignIn\FailedToSignIn;
use Kreait\Firebase\Auth\SignIn\GuzzleHandler;
use Kreait\Firebase\Auth\SignInAnonymously;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
final class GuzzleHandlerTest extends UnitTestCase
{
    private MockHandler $httpResponses;

    private SignIn $action;

    private GuzzleHandler $handler;

    protected function setUp(): void
    {
        $this->httpResponses = new MockHandler();
        $this->action = SignInAnonymously::new();

        $this->handler = new GuzzleHandler(new Client(['handler' => $this->httpResponses]));
    }

    public function testItFailsOnAnUnsupportedAction(): void
    {
        $this->expectException(FailedToSignIn::class);
        $this->handler->handle($this->createMock(SignIn::class));
    }

    public function testItFailsWhenGuzzleFails(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->method('send')->willThrowException($this->createMock(ConnectException::class));

        $handler = new GuzzleHandler($client);

        $this->expectException(FailedToSignIn::class);
        $handler->handle($this->action);
    }

    public function testItFailsOnAnUnsuccessfulResponse(): void
    {
        $this->httpResponses->append($response = new Response(400));

        try {
            $this->handler->handle($this->action);
        } catch (FailedToSignIn $e) {
            $this->assertSame($response, $e->response());
            $this->assertSame($this->action, $e->action());
        }
    }

    public function testItFailsOnASuccessfulResponseWithInvalidJson(): void
    {
        $this->httpResponses->append(new Response(200, [], '{'));

        $this->expectException(FailedToSignIn::class);
        $this->handler->handle($this->action);
    }

    public function testItWorks(): void
    {
        $this->httpResponses->append(new Response(200, [], (string) \json_encode([
            'id_token' => 'id_token',
            'refresh_token' => 'refresh_token',
            'access_token' => 'access_token',
            'expires_in' => 3600,
        ], JSON_FORCE_OBJECT)));

        $this->handler->handle($this->action);
        $this->addToAssertionCount(1);
    }
}
