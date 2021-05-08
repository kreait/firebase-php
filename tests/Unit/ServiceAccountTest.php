<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Tests\UnitTestCase;
use stdClass;

/**
 * @internal
 */
class ServiceAccountTest extends UnitTestCase
{
    private string $pathToUnreadableJson;

    private string $pathToValidJson;

    private string $validJson;

    /** @var array<string, string> */
    private array $validData;

    protected function setUp(): void
    {
        $this->pathToUnreadableJson = self::$fixturesDir.'/ServiceAccount/unreadable.json';
        @\chmod($this->pathToUnreadableJson, 0000);

        $this->pathToValidJson = self::$fixturesDir.'/ServiceAccount/valid.json';
        $this->validJson = (string) \file_get_contents($this->pathToValidJson);
        $this->validData = \json_decode($this->validJson, true);
    }

    protected function tearDown(): void
    {
        @\chmod($this->pathToUnreadableJson, 0644);
    }

    public function testCreateFromJsonText(): void
    {
        $serviceAccount = ServiceAccount::fromValue($this->validJson);
        $this->assertSame($this->validData, $serviceAccount->asArray());
    }

    public function testCreateFromJsonFile(): void
    {
        $serviceAccount = ServiceAccount::fromValue($this->pathToValidJson);
        $this->assertSame($this->validData, $serviceAccount->asArray());
    }

    public function testCreateFromMissingFile(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ServiceAccount::fromValue('missing.json');
    }

    public function testCreateFromDirectory(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ServiceAccount::fromValue(__DIR__);
    }

    public function testCreateFromUnreadableFile(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ServiceAccount::fromValue($this->pathToUnreadableJson);
    }

    public function testCreateFromArray(): void
    {
        $serviceAccount = ServiceAccount::fromValue($this->validData);
        $this->assertSame($this->validData, $serviceAccount->asArray());
    }

    public function testCreateFromArrayWithMissingTypeField(): void
    {
        $data = $this->validData;
        unset($data['type']);

        $this->expectException(InvalidArgumentException::class);
        ServiceAccount::fromValue($data);
    }

    public function testCreateFromServiceAccount(): void
    {
        $serviceAccount = $this->createMock(ServiceAccount::class);

        $this->assertSame($serviceAccount, ServiceAccount::fromValue($serviceAccount));
    }

    /**
     * @dataProvider invalidValues
     *
     * @param mixed $value
     */
    public function testCreateFromInvalidValue($value): void
    {
        $this->expectException(InvalidArgumentException::class);
        ServiceAccount::fromValue($value);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function invalidValues(): array
    {
        return [
            'true' => [true],
            'false' => [false],
            'malformed_json' => ['{'],
            'empty_json' => ['{}'],
            'empty_array' => [[]],
            'invalid_type' => [['type' => 'invalid']],
            'unsupported' => [new stdClass()],
        ];
    }
}
