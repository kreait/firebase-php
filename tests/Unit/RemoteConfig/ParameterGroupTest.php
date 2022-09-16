<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\RemoteConfig;

use Kreait\Firebase\RemoteConfig\Parameter;
use Kreait\Firebase\RemoteConfig\ParameterGroup;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ParameterGroupTest extends TestCase
{
    public function testItCanBeCreated(): void
    {
        $group = ParameterGroup::named($name = 'name')
            ->withDescription($description = 'description')
            ->withParameter($first = Parameter::named('first'))
            ->withParameter($second = Parameter::named('second'));

        self::assertSame($name, $group->name());
        self::assertSame($description, $group->description());

        self::assertContains($first, $group->parameters());
        self::assertContains($second, $group->parameters());
    }
}
