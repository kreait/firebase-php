<?php

namespace Tests\Firebase\Util;

use Firebase\Exception\InvalidArgumentException;
use Firebase\Util\JSON;
use Tests\FirebaseTestCase;

class JSONTest extends FirebaseTestCase
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
}
