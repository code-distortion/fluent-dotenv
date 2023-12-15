<?php

namespace CodeDistortion\FluentDotEnv\Misc;

use Throwable;

/**
 * Support class to retrieve from getenv() and replace its values.
 */
class GetenvSupport
{
    /**
     * Get the full list of current getenv() values.
     *
     * @return array<string, string>
     */
    public static function getAllGetenvVariables(): array
    {
        if ((!function_exists('getenv')) || (!function_exists('putenv'))) {
            return [];
        }

        // getenv() requires a $key to be passed to it before PHP 7.1
        if (version_compare(PHP_VERSION, '7.1.0', '<')) {
            return self::getParticularGetenvVariables(array_keys($_ENV));
        }

        return @(array) getenv();
    }

    /**
     * Get the current getenv() values for the given keys.
     *
     * @param string[] $keys The keys to return values for.
     * @return array<string, string>
     */
    public static function getParticularGetenvVariables(array $keys): array
    {
        if ((!function_exists('getenv')) || (!function_exists('putenv'))) {
            return [];
        }

        $values = [];
        foreach ($keys as $key) {
            $value = getenv($key);
            if ($value !== false) {
                $values[$key] = $value;
            }
        }
        return $values;
    }

    /**
     * Remove particular getenv() values.
     *
     * @param string[] $keys The keys to remove.
     * @return void
     */
    public static function removeGetenvVariables(array $keys)
    {
        foreach ($keys as $key) {
            putenv($key); // clear existing
        }
    }

    /**
     * Set new getenv() values.
     *
     * @param array<string, string> $values The values to add to getenv().
     * @return void
     */
    public static function addGetenvVariables(array $values)
    {
        foreach ($values as $key => $value) {
            putenv("$key=$value");
        }
    }

    /**
     * Set getenv() to contain the given values. This replaces the current set of values.
     *
     * @param array<string, string> $newValues The values to replace getenv() values with.
     * @return void
     */
    public static function replaceAllGetenvVariables(array $newValues)
    {
        if ((!function_exists('getenv')) || (!function_exists('putenv'))) {
            return;
        }

        $currentValues = self::getAllGetenvVariables();

        $allKeys = array_unique(
            array_merge(
                array_keys($currentValues),
                array_keys($newValues)
            )
        );

        foreach ($allKeys as $key) {

            // add or keep
            if (array_key_exists($key, $newValues)) {

                // update if different
                $value = $newValues[$key];
                if ((!isset($currentValues[$key])) || ($currentValues[$key] !== $value)) {
                    putenv("$key=$value");
                }
            } else {

                // remove
                putenv((string) $key);
            }
        }
    }
}
