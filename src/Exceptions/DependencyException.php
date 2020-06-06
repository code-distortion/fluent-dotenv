<?php

namespace CodeDistortion\FluentDotEnv\Exceptions;

/**
 * The exception thrown when a dependency could not be detected.
 */
class DependencyException extends FluentDotEnvException
{
    /**
     * A dot-env package  could not be found.
     *
     * @return self
     */
    public static function dotEnvReaderPackageNotDetected(): self
    {
        return new self('Could not detect a supported dot-env package or version');
    }
}
