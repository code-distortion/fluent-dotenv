<?php

namespace CodeDistortion\FluentDotEnv\Exceptions;

use Exception;

/**
 * Exception caused when load is called but .env files have already been loaded
 */
class AlreadyLoadedException extends FluentDotEnvException
{
    /**
     * load() or safeLoad() were called a second time.
     *
     * @return self
     */
    public static function alreadyLoaded(): self
    {
        return new self(
            '.env data has already been loaded. '
            . 'load() and safeload() may be called once with multiple files instead'
        );
    }
}
