<?php

namespace CodeDistortion\FluentDotEnv\DotEnvAdapters;

use CodeDistortion\FluentDotEnv\Exceptions\GeneralException;
use CodeDistortion\FluentDotEnv\Exceptions\InvalidPathException;
use CodeDistortion\FluentDotEnv\Misc\GetenvSupport;
use CodeDistortion\FluentDotEnv\Misc\ValueStore;
use Throwable;

/**
 * Abstract adapter to read .env files.
 */
abstract class AbstractDotEnvAdapter implements DotEnvAdapterInterface
{
    /** @var array<string, string> The original set of getenv() values. */
    private $origGetEnv = [];

    /** @var array<string, string> The original set of $_ENV values. */
    private $origEnv = [];

    /** @var array<string, string> The original set of $_SERVER values. */
    private $origServer = [];



    /**
     * Work out if the import process will update the getenv() values.
     *
     * If it doesn't then the process of backing up and clearing the getenv() values can be skipped.
     *
     * @return boolean
     */
    abstract protected function importWillUpdateGetenvValues(): bool;

    /**
     * When going through the process of backing up and clearing the getenv() values, work out if the code should touch
     * only the variables defined in the .env file (which requires it to be loaded an extra time beforehand).
     *
     * PHP 7.0 and below can't get a list of the current env vars using getenv() (with no arguments).
     *
     * So getting the keys from the .env file allows us to override those values and replace them after without needing
     * to know the full list.
     *
     * @return boolean
     */
    protected function shouldOnlyWorkWithVariablesDefinedInEnvFile(): bool
    {
        // look for PHP 7.0 or below
        return (bool) version_compare(PHP_VERSION, '7.1.0', '<');
    }



    /**
     * Load the values from the given .env file, and return them.
     *
     * NOTE: This MUST leave the getenv(), $_ENV, $_SERVER etc values as they were to begin with.
     *
     * @param string $path The path to the .env file.
     * @return ValueStore
     * @throws InvalidPathException When the directory or file could not be used.
     * @throws Throwable            Rethrows any other Throwable exception.
     */
    public function import(string $path): ValueStore
    {
        $path = $this->normalisePathSeparators($path);

        try {

            $this->recordCurrentEnvValues($path);
            $this->removeCurrentEnvValues();
            $valueStore = $this->importValuesFromEnvFile($path);

        } catch (Throwable $e) {

            throw $this->exceptionIsBecauseFileCantBeOpened($e)
                ? InvalidPathException::invalidPath($path, $e)
                : $e;

        } finally {

            $valueStore = $valueStore ?? new ValueStore();

            $keysJustOverridden = array_keys($valueStore->all());
            $this->restoreOriginalEnvValues($keysJustOverridden);
        }

        return $valueStore;
    }



    /**
     * Normalise the separators in a path, so they're the same on any OS.
     *
     * @param string $path The path to normalise.
     * @return string
     */
    private function normalisePathSeparators(string $path): string
    {
        return str_replace('\\', '/', $path);
    }



    /**
     * Record the current environment values, to be restored later.
     *
     * @param string $path The path to the .env file.
     * @return void
     */
    private function recordCurrentEnvValues(string $path)
    {
        $this->origEnv = $_ENV;
        $this->origServer = $_SERVER;

        if (!$this->importWillUpdateGetenvValues()) {
            return;
        }

        $this->origGetEnv = $this->shouldOnlyWorkWithVariablesDefinedInEnvFile()
            ? $this->resolveCurrentEnvVarsBasedOnKeysDefinedInEnvFile($path)
            : GetenvSupport::getAllGetenvVariables();
    }

    /**
     * Generate an array of the current env variables, based on the keys defined in the .env file.
     *
     * @param string $path The path to the .env file.
     * @return array<string, string>
     */
    private function resolveCurrentEnvVarsBasedOnKeysDefinedInEnvFile(string $path): array
    {
        $keys = $this->determineKeysDefinedInEnvFile($path);
        return GetenvSupport::getParticularGetenvVariables($keys);
    }

    /**
     * Look into a dotenv file, and find out which keys it defines.
     *
     * @param string $path The path to the .env file.
     * @return string[]
     */
    private function determineKeysDefinedInEnvFile(string $path): array
    {
        $envFileValues = $this->parseEnvFileForValues($path);

        return array_keys($envFileValues);
    }

    /**
     * Parse the contents of a .env file for key value pairs.
     *
     * Used when using PHP 7.0 or below, to determine which keys are in the .env file.
     *
     * @param string $path The path to the .env file.
     * @return array<string, mixed>
     */
    protected function parseEnvFileForValues(string $path): array
    {
        throw GeneralException::pleaseOverrideMethodInChildClass(static::class, __FUNCTION__);
    }



    /**
     * Remove all current environment values.
     *
     * @return void
     */
    private function removeCurrentEnvValues()
    {
        $_ENV = $_SERVER = [];

        if (!$this->importWillUpdateGetenvValues()) {
            return;
        }

        $origGetEnvKeys = array_keys($this->origGetEnv);
        GetenvSupport::removeGetenvVariables($origGetEnvKeys);
    }



    /**
     * Read the data from the given .env path.
     *
     * @param string $path The path to the .env file.
     * @return ValueStore
     */
    abstract protected function importValuesFromEnvFile(string $path): ValueStore;



    /**
     * Check if the given exception is because the .env file could not be opened.
     *
     * @param Throwable $e The exception to check.
     * @return boolean
     */
    abstract protected function exceptionIsBecauseFileCantBeOpened(Throwable $e): bool;



    /**
     * Restore the original environment values.
     *
     * @param string[] $keysJustOverridden The keys that were just overridden.
     * @return void
     */
    private function restoreOriginalEnvValues(array $keysJustOverridden)
    {
        $_ENV = $this->origEnv;
        $_SERVER = $this->origServer;

        if (!$this->importWillUpdateGetenvValues()) {
            return;
        }

        // PHP 7.1 and 7.2 on Windows don't pick up keys with empty values
        // so explicitly remove the values here in case any were empty
        GetenvSupport::removeGetenvVariables($keysJustOverridden);

        $this->shouldOnlyWorkWithVariablesDefinedInEnvFile()
            ? GetenvSupport::addGetenvVariables($this->origGetEnv)
            : GetenvSupport::replaceAllGetenvVariables($this->origGetEnv);
    }
}
