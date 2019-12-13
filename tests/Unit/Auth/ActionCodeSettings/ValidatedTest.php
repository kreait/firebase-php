<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Auth\ActionCodeSettings;

use InvalidArgumentException;
use Kreait\Firebase\Auth\ActionCodeSettings\Validated;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ValidatedTest extends TestCase
{
    /** @test */
    public function it_works_with_exactly_valid_settings()
    {
        $input = [
            'continueUrl' => 'https://domain.tld',
            'canHandleCodeInApp' => true,
            'dynamicLinkDomain' => 'https://dynamic.tld',
            'androidPackageName' => 'locale.vendor.name',
            'androidMinimumVersion' => '1.0',
            'androidInstallApp' => true,
            'iOSBundleId' => 'id.tld.domain.subdomain',
        ];

        $this->assertEquals($input, Validated::fromArray($input)->toArray());
    }

    /** @test */
    public function it_rejects_invalid_settings()
    {
        $this->expectException(InvalidArgumentException::class);
        Validated::fromArray(['foo' => 'bar']);
    }

    /** @test */
    public function it_can_be_empty()
    {
        $this->assertEmpty(Validated::empty()->toArray());
    }
}
