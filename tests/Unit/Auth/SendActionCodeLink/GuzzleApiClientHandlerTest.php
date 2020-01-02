<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Auth\SendActionCodeLink;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Response;
use Kreait\Firebase\Auth\ActionCodeSettings\ValidatedActionCodeSettings;
use Kreait\Firebase\Auth\CreateActionLink;
use Kreait\Firebase\Auth\SendActionLink;
use Kreait\Firebase\Auth\SendActionLink\FailedToSendActionLink;
use Kreait\Firebase\Auth\SendActionLink\GuzzleApiClientHandler;
use Kreait\Firebase\Value\Email;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

/**
 * @internal
 */
final class GuzzleApiClientHandlerTest extends TestCase
{
    private $client;

    /** @var SendActionLink */
    private $sendAction;

    /** @var GuzzleApiClientHandler */
    private $handler;

    protected function setUp()
    {
        $this->client = $this->prophesize(ClientInterface::class);

        $this->sendAction = new SendActionLink(
            CreateActionLink::new('SOME_TYPE', new Email('user@domain.tld'), ValidatedActionCodeSettings::empty())
        );

        $this->handler = new GuzzleApiClientHandler($this->client->reveal());
    }

    /** @test */
    public function it_handles_an_unknown_guzzle_error()
    {
        $this->client->send(Argument::cetera())->willThrow(new TransferException('Something happened'));

        $this->expectException(FailedToSendActionLink::class);
        $this->handler->handle($this->sendAction);
    }

    /** @test */
    public function it_fails_on_unsuccessful_responses()
    {
        $this->client->send(Argument::cetera())->willReturn(new Response(400));

        $this->expectException(FailedToSendActionLink::class);
        $this->handler->handle($this->sendAction);
    }

    /** @test */
    public function exceptions_contain_the_action_and_a_response()
    {
        $this->client->send(Argument::cetera())->willReturn($response = new Response(400));

        try {
            $this->handler->handle($this->sendAction);
            $this->fail('An exception should have been thrown');
        } catch (FailedToSendActionLink $e) {
            $this->assertSame($this->sendAction, $e->action());
            $this->assertSame($response, $e->response());
        }
    }
}
