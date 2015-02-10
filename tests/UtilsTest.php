<?php
/**
 * This file is part of the firebase-php package.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */
namespace Kreait\Firebase;

class UtilsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string      $given
     * @param string|null $expected
     *
     * @dataProvider locationProvider
     */
    public function testNormalizeLocation($given, $expected = null)
    {
        $this->assertEquals($expected ?: $given, Utils::normalizeLocation($given));
    }

    public function testNormalizeInvalidLocation()
    {
        $max = Restrictions::MAXIMUM_DEPTH_OF_CHILD_NODES;
        $maxPlusOne = $max + 1;

        $this->setExpectedException(
            '\Kreait\Firebase\Exception\FirebaseException',
            sprintf('A location key must not have more than %s keys, %s given.', $max, $maxPlusOne)
        );

        Utils::normalizeLocation(str_pad('', $maxPlusOne * 2, "x/"));
    }

    /**
     * @param string $location
     * @dataProvider locationsWithInvalidCharProvider
     */
    public function testNormalizeLocationWithInvalidChar($location)
    {
        $this->setExpectedException(
            '\Kreait\Firebase\Exception\FirebaseException',
            sprintf(
                'The location key "%s" contains on of the following invalid characters: %s',
                $location,
                Restrictions::FORBIDDEN_NODE_KEY_CHARS)
        );

        Utils::normalizeLocation($location);
    }

    public function testNormalizeLocationWithTooLongNodeKey()
    {
        $max = Restrictions::KEY_LENGTH_IN_BYTES;
        $maxPlusOne = $max + 1;

        $location = str_pad('', $maxPlusOne, 'x');

        $this->setExpectedException(
            '\Kreait\Firebase\Exception\FirebaseException',
            sprintf(
                'A location key must not be longer than %s bytes, %s bytes given.',
                $max,
                $maxPlusOne
            )
        );

        Utils::normalizeLocation($location);
    }

    /**
     * @param string      $given
     * @param string|null $expected
     *
     * @dataProvider baseUrlProvider
     */
    public function testNormalizeBaseUrl($given, $expected = null)
    {
        $this->assertEquals($expected ?: $given, Utils::normalizeBaseUrl($given));
    }

    /**
     * @expectedException \Kreait\Firebase\Exception\FirebaseException
     * @expectedExceptionMessage The base url must point to an https URL, "http://foo.bar" given.
     */
    public function testNormalizeNonHttpsBaseUrl()
    {
        Utils::normalizeBaseUrl('http://foo.bar');
    }

    /**
     * @expectedException \Kreait\Firebase\Exception\FirebaseException
     * @expectedExceptionMessage The url "invalid_base_url" is invalid.
     */
    public function testNormalizeInvalidBaseUrl()
    {
        Utils::normalizeBaseUrl('invalid_base_url');
    }

    public function locationProvider()
    {
        return [
            [''],
            ['location'],
            ['location/with/a/certain/depth'],
            ['/location/should/not/have/leading/slash', 'location/should/not/have/leading/slash'],
            ['location/should/not/have/trailing/slash/', 'location/should/not/have/trailing/slash'],
        ];
    }

    public function baseUrlProvider()
    {
        return [
            ['https://foo.bar'],
            ['https://foo.bar/', 'https://foo.bar'],
        ];
    }

    public function locationsWithInvalidCharProvider()
    {
        $chars = str_split(Restrictions::FORBIDDEN_NODE_KEY_CHARS);
        $array = [];
        foreach ($chars as $c) {
            $array[] = [sprintf('%s%s%s', uniqid(), $c, uniqid())];
        }

        return $array;
    }
}
