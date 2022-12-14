<?php

declare(strict_types=1);
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagEssentials\Tests\Common;

class ReflectionHelper
{
    public static function getMethod(string $className, string $methodName): \ReflectionMethod
    {
        $method = (new \ReflectionClass($className))->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }

    public static function getProperty(string $className, string $propertyName): \ReflectionProperty
    {
        $property = (new \ReflectionClass($className))->getProperty($propertyName);
        $property->setAccessible(true);

        return $property;
    }

    public static function getPropertyValue(object $object, string $propertyName)
    {
        return static::getProperty(get_class($object), $propertyName)->getValue($object);
    }
}
