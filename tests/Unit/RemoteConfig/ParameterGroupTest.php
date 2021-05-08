<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\RemoteConfig;

use Kreait\Firebase\RemoteConfig\Parameter;
use Kreait\Firebase\RemoteConfig\ParameterGroup;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ParameterGroupTest extends TestCase
{
    public function testItCanBeCreated(): void
    {
        $group = ParameterGroup::named($name = 'name')
            ->withDescription($description = 'description')
            ->withParameter($first = Parameter::named('first'))
            ->withParameter($second = Parameter::named('second'))
        ;

        $this->assertSame($name, $group->name());
        $this->assertSame($description, $group->description());

        $this->assertContains($first, $group->parameters());
        $this->assertContains($second, $group->parameters());
    }
}
