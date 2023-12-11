<?php

namespace CodeDistortion\FluentDotEnv\DotEnvAdapters\VLucas\Support;

use Dotenv\Dotenv;

/**
 * The vlucas/phpdotenv v2 Dotenv class, updated to use VLucasV2Loader instead of the default Loader.
 *
 * - this is done so it doesn't update $_ENV and getenv() values.
 */
class VLucasV2Dotenv extends Dotenv
{
    /**
     * Load `.env` file in given directory
     */
    public function load()
    {
        $this->loader = new VLucasV2Loader($this->filePath, $immutable = true);
        return $this->loader->load();
    }

    /**
     * Load `.env` file in given directory
     */
    public function overload()
    {
        $this->loader = new VLucasV2Loader($this->filePath, $immutable = false);
        return $this->loader->load();
    }
}
