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
    /** @var string */
    private $pathToUnreadableJson;

    /** @var string */
    private $pathToValidJson;

    /** @var string */
    private $validJson;

    /** @var array */
    private $validData;

    protected function setUp()
    {
        $this->pathToUnreadableJson = self::$fixturesDir.'/ServiceAccount/unreadable.json';
        @\chmod($this->pathToUnreadableJson, 0000);

        $this->pathToValidJson = self::$fixturesDir.'/ServiceAccount/valid.json';
        $this->validJson = (string) \file_get_contents($this->pathToValidJson);
        $this->validData = \json_decode($this->validJson, true);
    }

    protected function tearDown()
    {
        @\chmod($this->pathToUnreadableJson, 0644);
    }

    public function testCreateFromJsonText()
    {
        $serviceAccount = ServiceAccount::fromValue($this->validJson);
        $this->assertSame($this->validData, $serviceAccount->asArray());
    }

    public function testCreateFromJsonFile()
    {
        $serviceAccount = ServiceAccount::fromValue($this->pathToValidJson);
        $this->assertSame($this->validData, $serviceAccount->asArray());
    }

    public function testCreateFromMissingFile()
    {
        $this->expectException(InvalidArgumentException::class);
        ServiceAccount::fromValue('missing.json');
    }

    public function testCreateFromDirectory()
    {
        $this->expectException(InvalidArgumentException::class);
        ServiceAccount::fromValue(__DIR__);
    }

    public function testCreateFromUnreadableFile()
    {
        $this->expectException(InvalidArgumentException::class);
        ServiceAccount::fromValue($this->pathToUnreadableJson);
    }

    public function testCreateFromArray()
    {
        $serviceAccount = ServiceAccount::fromValue($this->validData);
        $this->assertSame($this->validData, $serviceAccount->asArray());
    }

    public function testCreateFromArrayWithMissingTypeField()
    {
        $data = $this->validData;
        unset($data['type']);

        $this->expectException(InvalidArgumentException::class);
        ServiceAccount::fromValue($data);
    }

    public function testCreateFromServiceAccount()
    {
        $serviceAccount = $this->createMock(ServiceAccount::class);

        $this->assertSame($serviceAccount, ServiceAccount::fromValue($serviceAccount));
    }

    /**
     * @see https://github.com/kreait/firebase-php/issues/228
     */
    public function testGetSanitizedProjectId()
    {
        $data = $this->validData;
        $data['project_id'] = 'example.com:api-project-xxxxxx';

        $serviceAccount = ServiceAccount::fromValue($data);

        $this->assertSame('example-com-api-project-xxxxxx', $serviceAccount->getSanitizedProjectId());
    }

    /**
     * @dataProvider invalidValues
     */
    public function testCreateFromInvalidValue($value)
    {
        $this->expectException(InvalidArgumentException::class);
        ServiceAccount::fromValue($value);
    }

    public function invalidValues()
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
