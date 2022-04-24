<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Helpers;

use ReflectionClass;

/**
 * Adapted from spatie/invade to be usable with PHP 7.4.
 *
 * @see https://github.com/spatie/invade
 *
 * @internal
 */
final class Invader
{
    public object $obj;
    /** @var ReflectionClass<object> */
    public ReflectionClass $reflected;

    public function __construct(object $obj)
    {
        $this->obj = $obj;
        $this->reflected = new ReflectionClass($obj);
    }

    /**
     * @return mixed
     */
    public function __get(string $name)
    {
        $property = $this->reflected->getProperty($name);

        $property->setAccessible(true);

        return $property->getValue($this->obj);
    }

    /**
     * @param mixed $value
     */
    public function __set(string $name, $value): void
    {
        $property = $this->reflected->getProperty($name);

        $property->setAccessible(true);

        $property->setValue($this->obj, $value);
    }

    /**
     * @param mixed[] $params
     *
     * @return mixed
     */
    public function __call(string $name, array $params = [])
    {
        $method = $this->reflected->getMethod($name);

        $method->setAccessible(true);

        return $method->invoke($this->obj, ...$params);
    }
}
