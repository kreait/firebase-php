<?php
namespace Kreait\Firebase\IdentityPlatform;

use Kreait\Firebase\Exception\InvalidArgumentException;

trait ParsesName
{
    public static function parseName(string $name)
    {
        $parts = explode('/', $name);
        $name = end($parts);
        static::validateName($name);
        return $name;
    }
    /**
     * Returns Name
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }
    /**
     * @internal
     *
     * @throws InvalidArgumentException
     * @param string $name
     * @return boolean
     */
    abstract public static function validateName(string $name) : bool ;
}
