<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Util;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Tests\UnitTestCase;
use Kreait\Firebase\Util\JSON;

/**
 * @internal
 */
final class JSONTest extends UnitTestCase
{
    public function testEncodeJson(): void
    {
        $this->assertSame(\json_encode(true), JSON::encode(true));
    }

    public function testEncodeInvalidJson(): void
    {
        $this->expectException(InvalidArgumentException::class);

        JSON::encode(INF);
    }

    public function testDecodeJson(): void
    {
        $this->assertSame(\json_decode('true', false), JSON::decode('true', false));
        $this->assertSame(\json_decode('true', true), JSON::decode('true', true));
    }

    public function testDecodeInvalidJson(): void
    {
        $this->expectException(InvalidArgumentException::class);

        JSON::decode('{');
    }

    public function testIsValid(): void
    {
        $this->assertTrue(JSON::isValid(\json_encode([])));
        $this->assertFalse(JSON::isValid('<html></html>'));
    }

    public function testPrettyPrint(): void
    {
        $data = ['foo' => 'bar'];

        $this->assertSame(\json_encode($data, JSON_PRETTY_PRINT), JSON::prettyPrint($data));
    }
}
