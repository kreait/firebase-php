<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Kreait\Firebase\Messaging\AppInstance;
use Kreait\Firebase\Messaging\RegistrationToken;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class AppInstanceTest extends TestCase
{
    /** @test */
    public function it_can_be_created_from_raw_data()
    {
        $token = RegistrationToken::fromValue('token');
        $data = [
            'application' => 'app-name',
            'applicationVersion' => '12345',
            'attestStatus' => 'ROOTED',
            'platform' => 'a-platform',
            'authorizedEntity' => 'this-is-the-project-id',
            'connectionType' => 'WIFI',
            'rel' => [
                'topics' => [
                    'first-topic' => [
                        'addDate' => '2019-01-01',
                    ],
                    'second-topic' => [
                        'addDate' => '2019-01-02',
                    ],
                ],
            ],
        ];

        $instance = AppInstance::fromRawData($token, $data);

        $this->assertEquals($data, $instance->rawData());
        $this->assertEquals($data, $instance->jsonSerialize());

        $this->assertCount(2, $instance->topicSubscriptions());
    }

    /** @test */
    public function it_accepts_numeric_topic_names()
    {
        $token = RegistrationToken::fromValue('token');
        $data = [
            'application' => 'app-name',
            'applicationVersion' => '12345',
            'attestStatus' => 'ROOTED',
            'platform' => 'a-platform',
            'authorizedEntity' => 'this-is-the-project-id',
            'connectionType' => 'WIFI',
            'rel' => [
                'topics' => [
                    '123' => [
                        'addDate' => '2019-01-01',
                    ],
                    456 => [
                        'addDate' => '2019-01-02',
                    ],
                ],
            ],
        ];

        $instance = AppInstance::fromRawData($token, $data);

        $this->assertEquals($data, $instance->rawData());
        $this->assertEquals($data, $instance->jsonSerialize());

        $this->assertCount(2, $instance->topicSubscriptions());
    }
}
