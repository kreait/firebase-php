<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Exception;

use Beste\Json;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Kreait\Firebase\Exception\AppCheck\ApiConnectionFailed;
use Kreait\Firebase\Exception\AppCheck\AppCheckError;
use Kreait\Firebase\Exception\AppCheck\PermissionDenied;
use Kreait\Firebase\Exception\AppCheckApiExceptionConverter;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use RuntimeException;
use Throwable;

/**
 * @internal
 */
final class AppCheckApiExceptionConverterTest extends TestCase
{
    private AppCheckApiExceptionConverter $converter;

    protected function setUp(): void
    {
        $this->converter = new AppCheckApiExceptionConverter();
    }

    public function testItConvertsAConnectException(): void
    {
        $connectException = new ConnectException(
            'curl error xx',
            $this->createMock(RequestInterface::class),
        );

        $this->assertInstanceOf(ApiConnectionFailed::class, $this->converter->convertException($connectException));
    }

    /**
     * @dataProvider exceptions
     *
     * @param class-string<object> $expectedClass
     */
    public function testItConvertsExceptions(Throwable $e, string $expectedClass): void
    {
        $converted = $this->converter->convertException($e);

        $this->assertInstanceOf($expectedClass, $converted);
    }

    /**
     * @return array<array<Throwable|class-string>>
     */
    public function exceptions(): array
    {
        return [
            'connection error' => [new ConnectException('Connection Failed', new Request('GET', 'https://domain.tld')), ApiConnectionFailed::class],
            '401' => [$this->createRequestException(401, 'Unauthenticated'), PermissionDenied::class],
            '403' => [$this->createRequestException(403, 'Unauthorized'), PermissionDenied::class],
            'runtime error' => [new RuntimeException('Something else'), AppCheckError::class],
        ];
    }

    public function createRequestException(int $code, string $identifier): RequestException
    {
        return new RequestException(
            'Firebase Error Test',
            new Request('GET', 'https://domain.tld'),
            new Response($code, [], Json::encode([
                'error' => [
                    'errors' => [
                        'domain' => 'global',
                        'reason' => 'invalid',
                        'message' => $identifier,
                    ],
                    'code' => $code,
                    'message' => 'Some error that might include the identifier "'.$identifier.'"',
                ],
            ])),
        );
    }
}
