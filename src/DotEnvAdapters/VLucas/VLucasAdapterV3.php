<?php

namespace CodeDistortion\FluentDotEnv\DotEnvAdapters\VLucas;

use CodeDistortion\FluentDotEnv\Exceptions\InvalidPathException;
use CodeDistortion\FluentDotEnv\Misc\ValueStore;
use Dotenv\Dotenv;
use Dotenv\Environment\Adapter\ServerConstAdapter;
use Dotenv\Environment\DotenvFactory;
use Dotenv\Exception\InvalidPathException as DotEnvInvalidPathException;
use Throwable;

/**
 * Adapter for vlucas/phpdotenv v3.
 */
class VLucasAdapterV3 extends AbstractVLucasAdapter
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
    public function import(string $path): ValueStore
    {
        $origServer = $_SERVER;

        try {
            $values = $this->runImport($path);
        } catch (Throwable $e) {

            throw ($e instanceof DotEnvInvalidPathException
                ? InvalidPathException::invalidPath($path, $e)
                : $e);

        } finally {
            $_SERVER = $origServer;
        }

        return $values;
    }

    /**
     * Actually import the data from the given .env path
     *
     * @param string $path The path to the .env file.
     * @return ValueStore
     */
    private function runImport(string $path): ValueStore
    {
        $_SERVER = [];

        list($directory, $filename) = $this->splitPath($path);

        // ImportAndPopulate determines what was imported based on $_SERVER
        // and chooses what to update based on that
        $factory = new DotenvFactory([new ServerConstAdapter()]);
        $dotenv = Dotenv::create($directory, $filename, $factory);
        $dotenv->load();

        return new ValueStore($_SERVER);
    }
}
