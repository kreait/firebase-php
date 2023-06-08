<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Auth;

use Beste\Json;
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

use const JSON_FORCE_OBJECT;

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

        $this->handler = new GuzzleHandler('my-project', new Client(['handler' => $this->httpResponses]));
    }

    /**
     * @test
     */
    public function itFailsOnAnUnsupportedAction(): void
    {
        $this->expectException(FailedToSignIn::class);
        $this->handler->handle($this->createMock(SignIn::class));
    }

    /**
     * @test
     */
    public function itFailsWhenGuzzleFails(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->method('send')->willThrowException($this->createMock(ConnectException::class));

        $handler = new GuzzleHandler('my-project', $client);

        $this->expectException(FailedToSignIn::class);
        $handler->handle($this->action);
    }

    /**
     * @test
     */
    public function itFailsOnAnUnsuccessfulResponse(): void
    {
        $this->httpResponses->append($response = new Response(400, [], '""'));

        try {
            $this->handler->handle($this->action);
        } catch (FailedToSignIn $e) {
            $this->assertSame($response, $e->response());
            $this->assertSame($this->action, $e->action());
        }
    }

    /**
     * @test
     */
    public function itFailsOnASuccessfulResponseWithInvalidJson(): void
    {
        $this->httpResponses->append(new Response(200, [], '{'));

        $this->expectException(FailedToSignIn::class);
        $this->handler->handle($this->action);
    }

    /**
     * @test
     */
    public function itWorks(): void
    {
        $this->httpResponses->append(new Response(200, [], Json::encode([
            'id_token' => 'id_token',
            'refresh_token' => 'refresh_token',
            'access_token' => 'access_token',
            'expires_in' => 3600,
        ], JSON_FORCE_OBJECT)));

        $this->handler->handle($this->action);
        $this->addToAssertionCount(1);
    }
}
