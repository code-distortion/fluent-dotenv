<?php

namespace CodeDistortion\FluentDotEnv\Exceptions;

/**
 * Miscellaneous exceptions.
 */
class GeneralException extends FluentDotEnvException
{
    /**
     * Throw an exception when a method is called that should have been overridden.
     *
     * @param string $class  The class that is missing the method.
     * @param string $method The method that is missing.
     * @return self
     */
    public static function pleaseOverrideMethodInChildClass(string $class, string $method): self
    {
        return new self("Please override the $method() method in class $class.");
    }
}
