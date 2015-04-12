<?php

/*
 * This file is part of the firebase-php package.
 *
 * (c) Jérôme Gamez <jerome@kreait.com>
 * (c) kreait GmbH <info@kreait.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * This file is part of the firebase-php package.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */
namespace Kreait\Firebase;

use Prophecy\Argument;
use Prophecy\Prophet;

class TestHelpers
{
    /**
     * Creates a mock reference.
     *
     * @param string|null $location
     * @param string|null $subLocation
     *
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    public static function createMockReference($location = null, $subLocation = null)
    {
        $prophet = new Prophet();
        $prophecy = $prophet->prophesize('Kreait\Firebase\ReferenceInterface');

        if (!$location) {
            return $prophecy;
        }

        $prophecy->getLocation()->willReturn($location);

        $locationParts = explode('/', $location);
        $prophecy->getKey()->willReturn(array_pop($locationParts));

        if (!count($locationParts) && (!is_string($subLocation) || strlen($subLocation) === 0)) {
            $prophecy->getReference(Argument::any())->willReturn(self::createMockReference(null));
        } else {
            $subLocationParts = explode('/', $subLocation);

            while (count($subLocationParts)) {
                $nextKey = array_shift($subLocationParts);
                $nextSubLocationString = implode('/', $subLocationParts);
                $subReference = self::createMockReference($nextKey, $nextSubLocationString);

                $prophecy->getReference($nextKey)->willReturn($subReference);
            }
        }

        return $prophecy;
    }
}
