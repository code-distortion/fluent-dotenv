<?php

namespace CodeDistortion\FluentDotEnv\DotEnvAdapters\VLucas;

use CodeDistortion\FluentDotEnv\DotEnvAdapters\DotEnvAdapterInterface;

/**
 * Adapter for vlucas/phpdotenv.
 */
abstract class AbstractVLucasAdapter implements DotEnvAdapterInterface
{
    /**
     * Split the given path into a directory and filename.
     *
     * @param string $path The path to the .env file.
     * @return string[]
     */
    protected function splitPath(string $path): array
    {
        $temp = explode('/', $path);
        $filename = array_pop($temp);
        $directory = implode('/', $temp);
        return [(string) $directory, (string) $filename];
    }
}
