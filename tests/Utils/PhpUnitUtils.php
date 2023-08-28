<?php

declare(strict_types=1);

namespace App\Tests\Utils;

class PhpUnitUtils
{
    /**
     * Helper function to call private/protected functions on classes.
     *
     * @param object $obj
     *   Instance of object to call function on
     * @param string $name
     *   Name of the function to call-
     * @param array $args
     *   Arguments for the function to call
     *
     * @return mixed
     *   Results of the invoked function
     *
     * @throws \ReflectionException
     */
    public static function callPrivateMethod(object $obj, string $name, array $args): mixed
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);

        return $method->invokeArgs($obj, $args);
    }
}
