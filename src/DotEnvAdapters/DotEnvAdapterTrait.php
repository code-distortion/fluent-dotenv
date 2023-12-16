<?php

namespace CodeDistortion\FluentDotEnv\DotEnvAdapters;

use Throwable;

/**
 * Methods sometimes used by the DotEnv adapters.
 */
trait DotEnvAdapterTrait
{
    /**
     * Pick the directory from a path.
     *
     * @param string $path The path to the .env file.
     * @return string
     */
    private function getDir(string $path): string
    {
        $temp = explode('/', $path);
        array_pop($temp); // remove the filename
        return implode(DIRECTORY_SEPARATOR, $temp);
    }

    /**
     * Pick the filename from a path.
     *
     * @param string $path The path to the .env file.
     * @return string
     */
    private function getFilename(string $path): string
    {
        $temp = explode('/', $path);
        return array_pop($temp);
    }



    /**
     * Get the content of a file in the local filesystem.
     *
     * Suppresses any errors.
     *
     * @param string $path The path to the file.
     * @return string
     */
    private function getFileContent(string $path): string
    {
        $content = false;
        try {
            $content = @file_get_contents($path);
        } catch (Throwable $e) {
        }

        return is_string($content)
            ? $content
            : '';
    }
}
