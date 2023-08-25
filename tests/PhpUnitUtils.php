<?php

namespace App\tests;

class PhpUnitUtils
{
    /**
     * @throws \ReflectionException
     */
    public static function callPrivateMethod(object $obj, string $name, array $args)
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);

        return $method->invokeArgs($obj, $args);
    }
}
