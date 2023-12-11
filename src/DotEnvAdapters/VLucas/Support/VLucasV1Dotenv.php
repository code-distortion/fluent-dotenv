<?php

namespace CodeDistortion\FluentDotEnv\DotEnvAdapters\VLucas\Support;

use Dotenv;

/**
 * The vlucas/phpdotenv v1 Dotenv class, with an overridden setEnvironmentVariable() method.
 *
 * - this is done so it doesn't update $_ENV and getenv() values.
 */
class VLucasV1Dotenv extends Dotenv
{
    /**
     * Set a variable.
     *
     * Variable set using:
     * - putenv
     * - $_ENV
     * - $_SERVER.
     *
     * The environment variable value is stripped of single and double quotes.
     *
     * @param string      $name
     * @param string|null $value
     *
     * @return void
     */
    public static function setEnvironmentVariable($name, $value = null)
    {
        list($name, $value) = static::normaliseEnvironmentVariable($name, $value);

        // Don't overwrite existing environment variables if we're immutable
        // Ruby's dotenv does this with `ENV[key] ||= value`.
        if (static::$immutable === true && !is_null(static::findEnvironmentVariable($name))) {
            return;
        }

//        putenv("$name=$value");
//        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}
