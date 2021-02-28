<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Exception;

use DateTimeImmutable;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Kreait\Clock\FrozenClock;
use Kreait\Firebase\Exception\Messaging\ApiConnectionFailed;
use Kreait\Firebase\Exception\Messaging\AuthenticationError;
use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use Kreait\Firebase\Exception\Messaging\MessagingError;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Exception\Messaging\QuotaExceeded;
use Kreait\Firebase\Exception\Messaging\ServerError;
use Kreait\Firebase\Exception\Messaging\ServerUnavailable;
use Kreait\Firebase\Exception\MessagingApiExceptionConverter;
use Kreait\Firebase\Util\JSON;
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

    /** @var FrozenClock */
    private $clock;

    protected function setUp(): void
    {
        $this->clock = new FrozenClock(new DateTimeImmutable());
        $this->converter = new MessagingApiExceptionConverter($this->clock);
    }

    /**
     * @test
     */
    public function it_converts_a_connect_exception(): void
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
     *
     * @param class-string<object> $expectedClass
     */
    public function it_converts_exceptions(Throwable $e, string $expectedClass): void
    {
        $converted = $this->converter->convertException($e);

        $this->assertInstanceOf($expectedClass, $converted);
    }

    public function exceptions(): array
    {
        return [
            [new ConnectException('Connection Failed', new Request('GET', 'https://domain.tld')), ApiConnectionFailed::class],
            [$this->createRequestException(400, 'Bad request'), InvalidMessage::class],
            [$this->createRequestException(401, 'Unauthenticated'), AuthenticationError::class],
            [$this->createRequestException(403, 'Unauthorized'), AuthenticationError::class],
            [$this->createRequestException(404, 'Not Found'), NotFound::class],
            [$this->createRequestException(429, 'Too Many Requests'), QuotaExceeded::class],
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
            new Response($code, [], JSON::encode([
                'error' => [
                    'errors' => [
                        'domain' => 'global',
                        'reason' => 'invalid',
                        'message' => $identifier,
                    ],
                    'code' => $code,
                    'message' => 'Some error that might include the identifier "'.$identifier.'"',
                ],
            ])));
    }

    /**
     * @test
     */
    public function it_knows_when_to_retry_after_with_seconds(): void
    {
        $response = new Response(429, ['Retry-After' => 60]);

        /** @var QuotaExceeded $converted */
        $converted = $this->converter->convertResponse($response);

        $expected = $this->clock->now()->modify('+60 seconds');

        $this->assertInstanceOf(QuotaExceeded::class, $converted);
        $this->assertInstanceOf(DateTimeImmutable::class, $converted->retryAfter());
        $this->assertSame($expected->getTimestamp(), $converted->retryAfter()->getTimestamp());
    }

    /**
     * @test
     */
    public function it_knows_when_to_retry_after_with_date_strings(): void
    {
        $expected = $this->clock->now()->modify('+60 seconds');

        $response = new Response(503, ['Retry-After' => $expected->format(\DATE_ATOM)]);

        /** @var ServerUnavailable $converted */
        $converted = $this->converter->convertResponse($response);

        $this->assertInstanceOf(ServerUnavailable::class, $converted);
        $this->assertInstanceOf(DateTimeImmutable::class, $converted->retryAfter());
        $this->assertSame($expected->getTimestamp(), $converted->retryAfter()->getTimestamp());
    }

    public function it_does_not_know_when_to_retry_when_it_does_not_have_to(): void
    {
        $response = new Response(503); // no Retry-After

        /** @var ServerUnavailable $converted */
        $converted = $this->converter->convertResponse($response);

        $this->assertInstanceOf(ServerUnavailable::class, $converted);
        $this->assertNull($converted->retryAfter());
    }
}
