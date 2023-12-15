<?php

namespace CodeDistortion\FluentDotEnv\DotEnvAdapters\VLucas;

use CodeDistortion\FluentDotEnv\DotEnvAdapters\AbstractDotEnvAdapter;
use CodeDistortion\FluentDotEnv\DotEnvAdapters\DotEnvAdapterTrait;
use CodeDistortion\FluentDotEnv\DotEnvAdapters\VLucas\Support\VLucasV1Dotenv;
use CodeDistortion\FluentDotEnv\Misc\ValueStore;
use InvalidArgumentException;
use Throwable;

/**
 * Adapter for vlucas/phpdotenv v1.
 */
class VLucasAdapterV1 extends AbstractDotEnvAdapter
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

        $dotEnv = new VLucasV1Dotenv();
        if (method_exists($dotEnv, 'makeMutable')) {
            $dotEnv->makeMutable();
        }

        $dotEnv->load($directory, $filename);

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
        // custom class VLucasV1Dotenv overrides Dotenv's setEnvironmentVariable() method
        // to remove the putenv() and $_ENV lines. HOWEVER:
        // - version <= 1.0.6 doesn't use the setEnvironmentVariable() method in the first place
        // - version <= 1.0.9 has some issues in overriding setEnvironmentVariable()
        // - version ^1.1.0 is required in composer.json to avoid problems with these old versions

        // otherwise…
        // not needed because vlucas/phpdotenv v1 is being used in a way where
        // it doesn't update getenv() values, it only populates $_SERVER
        // - see use of overloaded VLucasV1Dotenv class above
        return false;
    }
}
