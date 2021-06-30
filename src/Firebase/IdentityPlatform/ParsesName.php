<?php
namespace Kreait\Firebase\IdentityPlatform;

use Kreait\Firebase\Exception\InvalidArgumentException;

trait ParsesName
{
    /**
     * Parses name with possibke Project/Tenant
     *
     * @param string $name
     * @return string
     */
    public static function parseName(string $name) : string
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
