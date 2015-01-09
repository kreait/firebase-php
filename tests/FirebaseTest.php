<?php
/**
 * This file is part of the firebase-php package.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Kreait\Firebase;

use Ivory\HttpAdapter\CurlHttpAdapter;
use Prophecy\PhpUnit\ProphecyTestCase;

class FirebaseTest extends ProphecyTestCase
{
    /**
     * @var Firebase
     */
    protected $f;

    protected function setUp()
    {
        parent::setUp();

        $this->f = new Firebase('https://example.com');
    }

    public function testDefaultState()
    {
        $this->assertAttributeInstanceOf('Ivory\HttpAdapter\CurlHttpAdapter', 'http', $this->f);
    }

    public function testInitialState()
    {
        $f = new Firebase('https://example.com', $http = new CurlHttpAdapter());

        $this->assertAttributeSame($http, 'http', $f);
    }
}
