<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Exception;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Kreait\Firebase\Exception\Messaging\ApiConnectionFailed;
use Kreait\Firebase\Exception\Messaging\AuthenticationError;
use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use Kreait\Firebase\Exception\Messaging\MessagingError;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Exception\Messaging\ServerError;
use Kreait\Firebase\Exception\Messaging\ServerUnavailable;
use Kreait\Firebase\Exception\MessagingApiExceptionConverter;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use RuntimeException;
use Throwable;

/**
 * @internal
 */
class MessagingApiExceptionConverterTest extends TestCase
{
    /** @var MessagingApiExceptionConverter */
    private $converter;

    protected function setUp()
    {
        $this->converter = new MessagingApiExceptionConverter();
    }

    /** @test */
    public function it_converts_a_connect_exception()
    {
        $connectException = new ConnectException(
            'curl error xx',
            $this->createMock(RequestInterface::class)
        );

        $this->assertInstanceOf(ApiConnectionFailed::class, $this->converter->convertException($connectException));
    }

    /**
     * @test
     * @dataProvider exceptions
     */
    public function it_converts_exceptions(Throwable $e, string $expectedClass)
    {
        $this->assertInstanceOf($expectedClass, $this->converter->convertException($e));
    }

    public function exceptions()
    {
        return [
            [new ConnectException('Connection Failed', new Request('GET', 'https://domain.tld')), ApiConnectionFailed::class],
            [$this->createRequestException(400, 'Bad request'), InvalidMessage::class],
            [$this->createRequestException(401, 'Unauthenticated'), AuthenticationError::class],
            [$this->createRequestException(403, 'Unauthorized'), AuthenticationError::class],
            [$this->createRequestException(404, 'Not Found'), NotFound::class],
            [$this->createRequestException(500, 'Server broken'), ServerError::class],
            [$this->createRequestException(503, 'Server unavailable'), ServerUnavailable::class],
            [$this->createRequestException(418, 'Some tea'), MessagingError::class],
            [new RuntimeException('Something else'), MessagingError::class],
        ];
    }

    public function createRequestException(int $code, string $identifier): RequestException
    {
        return new RequestException(
            'Firebase Error Test',
            new Request('GET', 'https://domain.tld'),
            new Response($code, [], \json_encode([
                'error' => [
                    'errors' => [
                        'domain' => 'global',
                        'reason' => 'invalid',
                        'message' => $identifier,
                    ],
                    'code' => $code,
                    'message' => 'Some error that might include the idenfier "'.$identifier.'"',
                ],
            ])));
    }
}
