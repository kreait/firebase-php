<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Value;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Value\Url;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

/**
 * @internal
 */
class UrlTest extends TestCase
{
    /**
     * @dataProvider validValues
     */
    public function testWithValidValue($value)
    {
        $url = Url::fromValue($value);

        $this->assertSame($value, (string) $url);
        $this->assertSame($value, $url->jsonSerialize());
        $this->assertTrue($url->equalsTo($value));
        $this->assertInstanceOf(UriInterface::class, $url->toUri());
    }

    /**
     * @dataProvider invalidValues
     */
    public function testWithInvalidValue($value)
    {
        $this->expectException(InvalidArgumentException::class);
        Url::fromValue($value);
    }

    public function validValues(): array
    {
        return [
            ['http://domain.tld'],
        ];
    }

    public function invalidValues(): array
    {
        return [
            ['http:///domain.tld'],
            ['http://:80'],
        ];
    }
}
