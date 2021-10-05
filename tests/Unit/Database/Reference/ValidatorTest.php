<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database\Reference;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database\Reference\Validator;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Tests\UnitTestCase;
use Psr\Http\Message\UriInterface;

/**
 * @internal
 */
class ValidatorTest extends UnitTestCase
{
    private UriInterface $uri;

    private Validator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->uri = new Uri('http://domain.tld');
        $this->validator = new Validator();
    }

    public function testValidateDepth(): void
    {
        $uri = $this->uri->withPath('/'.\str_pad('', (Validator::MAX_DEPTH + 1) * 2, 'x/'));

        $this->expectException(InvalidArgumentException::class);
        $this->validator->validateUri($uri);
    }

    public function testValidateKeySize(): void
    {
        $uri = $this->uri->withPath('/'.\str_pad('', Validator::MAX_KEY_SIZE + 1, 'x'));

        $this->expectException(InvalidArgumentException::class);
        $this->validator->validateUri($uri);
    }

    /**
     * @dataProvider invalidChars
     */
    public function testValidateChars(string $value): void
    {
        $uri = $this->uri->withPath('/'.\ltrim($value, '/'));

        $this->expectException(InvalidArgumentException::class);
        $this->validator->validateUri($uri);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function invalidChars(): array
    {
        return [
            '.' => ['.'],
            '$' => ['$'],
            '#' => ['#'],
            '[' => ['['],
            ']' => [']'],
        ];
    }
}
