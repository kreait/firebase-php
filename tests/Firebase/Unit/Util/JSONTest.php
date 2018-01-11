<?php

namespace Kreait\Tests\Firebase\Unit\Util;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Util\JSON;
use Kreait\Tests\Firebase\Unit\UnitTestCase;

class JSONTest extends UnitTestCase
{
    public function testEncodeJson()
    {
        $this->assertSame(\json_encode(true), JSON::encode(true));
    }

    public function testEncodeInvalidJson()
    {
        $this->expectException(InvalidArgumentException::class);

        JSON::encode(INF);
    }

    public function testDecodeJson()
    {
        $this->assertSame(\json_decode('true'), JSON::decode('true'));
    }

    public function testDecodeInvalidJson()
    {
        $this->expectException(InvalidArgumentException::class);

        JSON::decode('{');
    }

    public function testIsValid()
    {
        $this->assertTrue(JSON::isValid(json_encode([])));
        $this->assertFalse(JSON::isValid('<html></html>'));
    }
}
