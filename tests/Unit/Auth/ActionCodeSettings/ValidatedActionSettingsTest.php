<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Auth\ActionCodeSettings;

use InvalidArgumentException;
use Kreait\Firebase\Auth\ActionCodeSettings\ValidatedActionCodeSettings;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ValidatedActionSettingsTest extends TestCase
{
    /**
     * @param array<string, mixed> $input
     * @param array<string, mixed> $expected
     */
    #[DataProvider('validInputs')]
    #[Test]
    public function itWorksValidSettings(array $input, array $expected): void
    {
        $this->assertEqualsCanonicalizing($expected, ValidatedActionCodeSettings::fromArray($input)->toArray());
    }

    #[Test]
    public function itRejectsInvalidSettings(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ValidatedActionCodeSettings::fromArray(['foo' => 'bar']);
    }

    #[Test]
    public function itCanBeEmpty(): void
    {
        $this->assertEmpty(ValidatedActionCodeSettings::empty()->toArray());
    }

    public static function validInputs(): \Iterator
    {
        $continueUrl = 'https://example.com';
        yield 'full' => [
            [
                'continueUrl' => $continueUrl,
                'handleCodeInApp' => true,
                'dynamicLinkDomain' => 'https://dynamic.example.com',
                'androidPackageName' => 'locale.vendor.name',
                'androidMinimumVersion' => '1.0',
                'androidInstallApp' => true,
                'iOSBundleId' => 'id.tld.domain.subdomain',
            ],
            [
                'continueUrl' => $continueUrl,
                'canHandleCodeInApp' => true,
                'dynamicLinkDomain' => 'https://dynamic.example.com',
                'androidPackageName' => 'locale.vendor.name',
                'androidMinimumVersion' => '1.0',
                'androidInstallApp' => true,
                'iOSBundleId' => 'id.tld.domain.subdomain',
            ],
        ];
        yield 'url_alias' => [
            ['url' => $continueUrl],
            ['continueUrl' => $continueUrl],
        ];
        yield 'handle_to_can_handle' => [
            ['handleCodeInApp' => false],
            ['canHandleCodeInApp' => false],
        ];
    }
}
