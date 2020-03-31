<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Value;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Value\Url;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class UrlTest extends TestCase
{
    /**
     * @dataProvider validValues
     */
    public function testWithValidValue($value): void
    {
        $url = Url::fromValue($value);

        $check = (string) $value;

        $this->assertSame($check, (string) $url);
        $this->assertSame($check, (string) $url->toUri());
        $this->assertSame($check, $url->jsonSerialize());
        $this->assertTrue($url->equalsTo($check));
    }

    /**
     * @dataProvider invalidValues
     */
    public function testWithInvalidValue($value): void
    {
        $this->expectException(InvalidArgumentException::class);
        Url::fromValue($value);
    }

    public function validValues(): array
    {
        return [
            ['http://domain.tld'],
            [new Uri('http://domain.tld')],
            [new Url(new Uri('http://domain.tld'))],
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
