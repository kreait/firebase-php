<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Auth\ActionCodeSettings;

use InvalidArgumentException;
use Kreait\Firebase\Auth\ActionCodeSettings\ValidatedActionCodeSettings;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ValidatedActionSettingsTest extends TestCase
{
    /**
     * @test
     * @dataProvider validInputs
     */
    public function it_works_valid_settings($input, $expected): void
    {
        $this->assertEquals($expected, ValidatedActionCodeSettings::fromArray($input)->toArray());
    }

    /**
     * @test
     */
    public function it_rejects_invalid_settings(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ValidatedActionCodeSettings::fromArray(['foo' => 'bar']);
    }

    /**
     * @test
     */
    public function it_can_be_empty(): void
    {
        $this->assertEmpty(ValidatedActionCodeSettings::empty()->toArray());
    }

    public function validInputs()
    {
        $continueUrl = 'https://domain.tld';

        return [
            'full' => [
                [
                    'continueUrl' => $continueUrl,
                    'handleCodeInApp' => true,
                    'dynamicLinkDomain' => 'https://dynamic.tld',
                    'androidPackageName' => 'locale.vendor.name',
                    'androidMinimumVersion' => '1.0',
                    'androidInstallApp' => true,
                    'iOSBundleId' => 'id.tld.domain.subdomain',
                ],
                [
                    'continueUrl' => $continueUrl,
                    'canHandleCodeInApp' => true,
                    'dynamicLinkDomain' => 'https://dynamic.tld',
                    'androidPackageName' => 'locale.vendor.name',
                    'androidMinimumVersion' => '1.0',
                    'androidInstallApp' => true,
                    'iOSBundleId' => 'id.tld.domain.subdomain',
                ],
            ],
            'url_alias' => [
                ['url' => $continueUrl],
                ['continueUrl' => $continueUrl],
            ],
            'handle_to_can_handle' => [
                ['handleCodeInApp' => false],
                ['canHandleCodeInApp' => false],
            ],
        ];
    }
}
