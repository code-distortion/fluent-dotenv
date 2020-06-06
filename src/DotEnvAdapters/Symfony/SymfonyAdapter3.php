<?php

namespace CodeDistortion\FluentDotEnv\DotEnvAdapters\Symfony;

use CodeDistortion\FluentDotEnv\DotEnvAdapters\DotEnvAdapterInterface;
use CodeDistortion\FluentDotEnv\Exceptions\InvalidPathException;
use CodeDistortion\FluentDotEnv\Misc\GetenvSupport;
use CodeDistortion\FluentDotEnv\Misc\ValueStore;
use Symfony\Component\Dotenv\Dotenv;
use Throwable;
use Symfony\Component\Dotenv\Exception\PathException;

/**
 * Adapter for symfony/dotenv.
 */
class SymfonyAdapter3 implements DotEnvAdapterInterface
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
        $origGetEnv = GetenvSupport::getenvValues();
        $origEnv = $_ENV;
        $origServer = $_SERVER;

        try {
            $values = $this->runImport($path);
        } catch (Throwable $e) {

            throw ($e instanceof PathException
                ? InvalidPathException::invalidPath($path, $e)
                : $e);

        } finally {
            GetenvSupport::replaceGetenv($origGetEnv);
            $_ENV = $origEnv;
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
        foreach (GetenvSupport::getenvValues() as $key => $value) {
            putenv($key);
        }
        $_SERVER = $_ENV = [];

        (new Dotenv)->load($path);
        unset($_SERVER['SYMFONY_DOTENV_VARS']);

        return new ValueStore($_SERVER);
    }
}
