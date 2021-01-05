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
     * @return mixed[]
     */
    public static function getenvValues(): array
    {
        if ((!function_exists('getenv')) || (!function_exists('putenv'))) {
            return [];
        }

        // getenv requires a $key to be passed to it before PHP 7.1
        try {
            return (array) getenv();
        } catch (Throwable $e) {
        }

        // if needed, get the getenv() values individually
        $values = [];
        foreach (array_keys($_ENV) as $key) {
            $values[$key] = getenv($key);
        }
        return $values;
    }

    /**
     * Set getenv() to contain the given values.
     *
     * @param string[] $values The values to replace getenv() values with.
     * @return void
     */
    public static function replaceGetenv(array $values)
    {
        if ((!function_exists('getenv')) || (!function_exists('putenv'))) {
            return;
        }

        $currentValues = static::getenvValues();
        $allValues = array_merge($currentValues, $values);

        foreach (array_keys($allValues) as $key) {

            // add or keep
            if (array_key_exists($key, $values)) {

                // update if different
                $value = $values[$key];
                if ((!isset($currentValues[$key])) || ($currentValues[$key] != $value)) {
                    putenv("$key=$value");
                }
            } else {
                // remove
                putenv((string) $key);
            }
        }
    }
}
