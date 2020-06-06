<?php

namespace CodeDistortion\FluentDotEnv\DotEnvAdapters;

use CodeDistortion\FluentDotEnv\Exceptions\InvalidPathException;
use CodeDistortion\FluentDotEnv\Misc\ValueStore;
use Throwable;

/**
 * Interface for the dotenv adapters.
 */
interface DotEnvAdapterInterface
{
    /**
     * Load the values from the given .env file, and return them.
     *
     * NOTE: This MUST leave the getenv(), $_ENV, $_SERVER etc values as they were to begin with.
     *
     * @param string $path The path to the .env file.
     * @return ValueStore
     * @throws InvalidPathException When the directory or file could not be used.
     * @throws Throwable            Rethrows any Throwable exception.
     */
    public function import(string $path): ValueStore;
}
