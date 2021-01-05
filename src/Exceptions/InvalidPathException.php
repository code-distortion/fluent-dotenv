<?php

namespace CodeDistortion\FluentDotEnv\Exceptions;

use Exception;

/**
 * Exception caused when a path or file could not be found.
 */
class InvalidPathException extends FluentDotEnvException
{
    /**
     * Invalid path.
     *
     * @param string    $path The path to the directory or file.
     * @param Exception $e    The original exception that vlucas/phpdotenv threw.
     * @return self
     */
    public static function invalidPath(string $path, Exception $e): self
    {
        return new self("Unable to read from the \"$path\" environment file", 0, $e);
    }
}
