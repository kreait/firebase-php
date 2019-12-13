<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Auth\CreateActionCodeLink;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Response;
use Kreait\Firebase\Auth\ActionCodeSettings\ValidatedActionCodeSettings;
use Kreait\Firebase\Auth\CreateActionLink;
use Kreait\Firebase\Auth\CreateActionLink\FailedToCreateActionLink;
use Kreait\Firebase\Auth\CreateActionLink\GuzzleApiClientHandler;
use Kreait\Firebase\Value\Email;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

/**
 * @internal
 */
final class GuzzleApiClientHandlerTest extends TestCase
{
    private $client;

    /** @var CreateActionLink */
    private $action;

    /** @var GuzzleApiClientHandler */
    private $handler;

    protected function setUp()
    {
        $this->client = $this->prophesize(ClientInterface::class);
        $this->action = CreateActionLink::new('SOME_TYPE', new Email('user@domain.tld'), ValidatedActionCodeSettings::empty());

        $this->handler = new GuzzleApiClientHandler($this->client->reveal());
    }

    /** @test */
    public function it_handles_an_unknown_guzzle_error()
    {
        $this->client->send(Argument::cetera())->willThrow(new TransferException('Something happened'));

        $this->expectException(FailedToCreateActionLink::class);
        $this->handler->handle($this->action);
    }

    /** @test */
    public function it_fails_on_unsuccessful_responses()
    {
        $this->client->send(Argument::cetera())->willReturn(new Response(400));

        $this->expectException(FailedToCreateActionLink::class);
        $this->handler->handle($this->action);
    }

    /** @test */
    public function it_fails_on_unparseable_json_responses()
    {
        $this->client->send(Argument::cetera())->willReturn(new Response(200, [], ','));

        $this->expectException(FailedToCreateActionLink::class);
        $this->handler->handle($this->action);
    }

    /** @test */
    public function it_fails_on_unexpected_data()
    {
        $this->client->send(Argument::cetera())->willReturn(new Response(200, [], '{"no_oob_code": "nope"}'));

        $this->expectException(FailedToCreateActionLink::class);
        $this->handler->handle($this->action);
    }
}
