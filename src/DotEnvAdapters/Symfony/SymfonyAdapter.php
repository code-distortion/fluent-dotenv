<?php

namespace CodeDistortion\FluentDotEnv\DotEnvAdapters\Symfony;

use CodeDistortion\FluentDotEnv\DotEnvAdapters\AbstractDotEnvAdapter;
use CodeDistortion\FluentDotEnv\DotEnvAdapters\DotEnvAdapterTrait;
use CodeDistortion\FluentDotEnv\Misc\ValueStore;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Dotenv\Exception\PathException;
use Throwable;

/**
 * Adapter for symfony/dotenv.
 */
class SymfonyAdapter extends AbstractDotEnvAdapter
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
        $dotenv = new Dotenv();
        if (method_exists(Dotenv::class, 'usePutenv')) {
            $dotenv->usePutenv(false);
        }
        $dotenv->load($path);

        unset($_ENV['SYMFONY_DOTENV_VARS']); // symfony/dotenv sets this key

        return new ValueStore($_ENV);
    }



    /**
     * Check if the given exception is because the .env file could not be opened.
     *
     * @param Throwable $e The exception to check.
     * @return boolean
     */
    protected function exceptionIsBecauseFileCantBeOpened(Throwable $e): bool
    {
        return $e instanceof PathException;
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
        // updating of getenv() values will be turned off when this method is available
        return !method_exists(Dotenv::class, 'usePutenv');
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
        $content = $this->getFileContent($path);

        // luckily Symfony Dotenv's parse() method is public
        return (new Dotenv())->parse($content, 'doesnt-matter');
    }
}
