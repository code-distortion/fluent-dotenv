<?php

namespace CodeDistortion\FluentDotEnv\DotEnvAdapters\VLucas;

use CodeDistortion\FluentDotEnv\DotEnvAdapters\AbstractDotEnvAdapter;
use CodeDistortion\FluentDotEnv\DotEnvAdapters\DotEnvAdapterTrait;
use CodeDistortion\FluentDotEnv\Misc\ValueStore;
use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException as DotEnvInvalidPathException;
use Dotenv\Repository\Adapter\ServerConstAdapter;
use Dotenv\Repository\RepositoryBuilder;
use Throwable;

/**
 * Adapter for vlucas/phpdotenv v4.
 */
class VLucasAdapterV4 extends AbstractDotEnvAdapter
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

        // ImportAndPopulate determines what was imported based on $_SERVER
        // and chooses what to update based on that
        $adapters = [new ServerConstAdapter()];
        $repository = RepositoryBuilder::create()->withReaders($adapters)->withWriters($adapters)->make();
        Dotenv::create($repository, $directory, $filename)->load();

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
        return $e instanceof DotEnvInvalidPathException;
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
        // not needed because vlucas/phpdotenv v4 is being used in a way where
        // it doesn't update getenv() values, it only populates $_SERVER
        // - see use of ServerConstAdapter() above
        return false;
    }
}
