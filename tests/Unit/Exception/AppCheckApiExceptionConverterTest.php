<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Exception;

use Beste\Json;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Iterator;
use Kreait\Firebase\Exception\AppCheck\ApiConnectionFailed;
use Kreait\Firebase\Exception\AppCheck\AppCheckError;
use Kreait\Firebase\Exception\AppCheck\PermissionDenied;
use Kreait\Firebase\Exception\AppCheckApiExceptionConverter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function itConvertsAConnectException(): void
    {
        $connectException = new ConnectException(
            'curl error xx',
            $this->createMock(RequestInterface::class),
        );

        $this->assertInstanceOf(ApiConnectionFailed::class, $this->converter->convertException($connectException));
    }

    /**
     * @param class-string<object> $expectedClass
     */
    #[DataProvider('exceptions')]
    #[Test]
    public function itConvertsExceptions(Throwable $e, string $expectedClass): void
    {
        $converted = $this->converter->convertException($e);

        $this->assertInstanceOf($expectedClass, $converted);
    }

    public static function exceptions(): Iterator
    {
        yield 'connection error' => [new ConnectException('Connection Failed', new Request('GET', 'https://example.com')), ApiConnectionFailed::class];
        yield '401' => [self::createRequestException(401, 'Unauthenticated'), PermissionDenied::class];
        yield '403' => [self::createRequestException(403, 'Unauthorized'), PermissionDenied::class];
        yield 'runtime error' => [new RuntimeException('Something else'), AppCheckError::class];
    }

    public static function createRequestException(int $code, string $identifier): RequestException
    {
        return new RequestException(
            'Firebase Error Test',
            new Request('GET', 'https://example.com'),
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
