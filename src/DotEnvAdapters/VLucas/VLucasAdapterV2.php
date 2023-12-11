<?php

namespace CodeDistortion\FluentDotEnv\DotEnvAdapters\VLucas;

use CodeDistortion\FluentDotEnv\DotEnvAdapters\AbstractDotEnvAdapter;
use CodeDistortion\FluentDotEnv\DotEnvAdapters\DotEnvAdapterTrait;
use CodeDistortion\FluentDotEnv\DotEnvAdapters\VLucas\Support\VLucasV2Dotenv;
use CodeDistortion\FluentDotEnv\Misc\ValueStore;
use InvalidArgumentException;
use Throwable;

/**
 * Adapter for vlucas/phpdotenv v2.
 */
class VLucasAdapterV2 extends AbstractDotEnvAdapter
{
    use DotEnvAdapterTrait;



    /**
     * Read the data from the given .env path.
     *
     * @param string $path The path to the .env file.
     * @return ValueStore
     */
    protected function importValuesFromEnvFile(string $path): ValueStore
    {
        $directory = $this->getDir($path);
        $filename = $this->getFilename($path);

        (new VLucasV2Dotenv($directory, $filename))->overload();

        return new ValueStore($_SERVER);
    }



    /**
     * Check if the given exception is because the .env file could not be opened.
     *
     * @param Throwable $e The exception to check.
     * @return boolean
     */
    protected function exceptionIsBecauseFileCantBeOpened(Throwable $e): bool
    {
        return $e instanceof InvalidArgumentException;
    }



    /**
     * Work out if the import process will update the getenv() values.
     *
     * If it doesn't, then the process of backing up and clearing the getenv() values can be skipped.
     *
     * @return boolean
     */
    protected function importWillUpdateGetenvValues(): bool
    {
        // not needed because vlucas/phpdotenv v2 is being used in a way where
        // it doesn't update getenv() values, it only populates $_SERVER
        // - see use of overloaded VLucasV2Dotenv class above
        return false;
    }
}
