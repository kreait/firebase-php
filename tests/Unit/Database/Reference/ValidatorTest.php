<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database\Reference;

use GuzzleHttp\Psr7\Uri;
use Iterator;
use Kreait\Firebase\Database\Reference\Validator;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\UriInterface;

use function ltrim;
use function str_pad;

/**
 * @internal
 */
final class ValidatorTest extends UnitTestCase
{
    private UriInterface $uri;
    private Validator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->uri = new Uri('http://example.com');
        $this->validator = new Validator();
    }

    #[Test]
    public function validateDepth(): void
    {
        $uri = $this->uri->withPath('/'.str_pad('', (Validator::MAX_DEPTH + 1) * 2, 'x/'));

        $this->expectException(InvalidArgumentException::class);
        $this->validator->validateUri($uri);
    }

    #[Test]
    public function validateKeySize(): void
    {
        $uri = $this->uri->withPath('/'.str_pad('', Validator::MAX_KEY_SIZE + 1, 'x'));

        $this->expectException(InvalidArgumentException::class);
        $this->validator->validateUri($uri);
    }

    #[DataProvider('invalidChars')]
    #[Test]
    public function validateChars(string $value): void
    {
        $uri = $this->uri->withPath('/'.ltrim($value, '/'));

        $this->expectException(InvalidArgumentException::class);
        $this->validator->validateUri($uri);
    }

    public static function invalidChars(): Iterator
    {
        yield '.' => ['.'];
        yield '$' => ['$'];
        yield '#' => ['#'];
        yield '[' => ['['];
        yield ']' => [']'];
    }
}
