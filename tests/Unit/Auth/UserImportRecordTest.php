<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Auth;


use Kreait\Firebase\Auth\ImportUserRecord;
use Kreait\Firebase\Util\JSON;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class UserImportRecordTest extends TestCase
{
    /**
     * @dataProvider getExpectations()
     */
    public function testJsonSerialization(ImportUserRecord $record, string $expectedFormat): void
    {
        $this->assertEquals(JSON::encode($record), $expectedFormat);
    }

    public function getExpectations(): \Generator
    {
        yield [
            ImportUserRecord::new()
                ->withUid($uid = \bin2hex(\random_bytes(5)))
                ->withDisplayName($displayName = 'Some display name')
                ->withPhotoUrl($photoUrl = 'https://example.org/photo.jpg')
                ->withPhoneNumber($phoneNumber = '+1234567' . \random_int(1000, 9999))
                ->withVerifiedEmail($email = $uid . '@example.org')
                ->markTokensValidAfter($validSince = new \DateTimeImmutable())
                ->markAsEnabled()
                ->withCustomClaims($claims = ['admin' => true]),
            JSON::encode([
                'localId' => $uid,
                'email' => $email,
                'emailVerified' => true,
                'displayName' => $displayName,
                'disabled' => false,
                'phoneNumber' => $phoneNumber,
                'photoUrl' => $photoUrl,
                'customAttributes' => json_encode($claims),
                'validSince' => $validSince->format(DATE_ATOM),
            ])
        ];
    }
}
