<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Kreait\Firebase\Messaging\RegistrationToken;
use Kreait\Firebase\Messaging\Topic;
use PHPUnit\Framework\TestCase;

class RegistrationTokenTest extends TestCase
{
    /**
     * @dataProvider valueProvider
     */
    public function testFromValue($expected, $value)
    {
        $this->assertSame($expected, (string) RegistrationToken::fromValue($value)->value());
    }

    public function valueProvider()
    {
        return [
            ['foo', 'foo'],
        ];
    }
}
