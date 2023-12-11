<?php

namespace CodeDistortion\FluentDotEnv\DotEnvAdapters\VLucas\Support;

use Dotenv\Loader;

/**
 * The vlucas/phpdotenv v2 Loader class, with an overridden setEnvironmentVariable() method.
 *
 * - this is done so it doesn't update $_ENV and getenv() values.
 */
class VLucasV2Loader extends Loader
{
    /**
     * Set a variable using:
     * - putenv
     * - $_ENV
     * - $_SERVER
     *
     * The environment variable value is stripped of single and double quotes.
     *
     * @param $name
     * @param null $value
     */
    public function setEnvironmentVariable($name, $value = null)
    {
        list($name, $value) = $this->normaliseEnvironmentVariable($name, $value);

        // Don't overwrite existing environment variables if we're immutable
        // Ruby's dotenv does this with `ENV[key] ||= value`.
        if ($this->immutable === true && !is_null($this->getEnvironmentVariable($name))) {
            return;
        }

//        putenv("$name=$value");
//        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}
