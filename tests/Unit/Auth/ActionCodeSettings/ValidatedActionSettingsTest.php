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
     * @dataProvider validInputs
     *
     * @param array<string, mixed> $input
     * @param array<string, mixed> $expected
     */
    public function testItWorksValidSettings(array $input, array $expected): void
    {
        $this->assertEquals($expected, ValidatedActionCodeSettings::fromArray($input)->toArray());
    }

    public function testItRejectsInvalidSettings(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ValidatedActionCodeSettings::fromArray(['foo' => 'bar']);
    }

    public function testItCanBeEmpty(): void
    {
        $this->assertEmpty(ValidatedActionCodeSettings::empty()->toArray());
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function validInputs(): array
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
