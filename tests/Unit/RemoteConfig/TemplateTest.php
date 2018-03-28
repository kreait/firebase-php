<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\RemoteConfig;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\RemoteConfig\ConditionalValue;
use Kreait\Firebase\RemoteConfig\Parameter;
use Kreait\Firebase\RemoteConfig\TagColor;
use Kreait\Firebase\RemoteConfig\Template;
use Kreait\Firebase\Tests\UnitTestCase;

class TemplateTest extends UnitTestCase
{
    public function testCreateWithInvalidConditionalValue()
    {
        $parameter = Parameter::named('foo')->withConditionalValue(new ConditionalValue('non_existing_condition', 'false'));

        $this->expectException(InvalidArgumentException::class);
        Template::new()->withParameter($parameter);
    }
}
