<?php

namespace CodeDistortion\FluentDotEnv\Exceptions;

/**
 * The exception thrown when validation fails.
 */
class ValidationException extends FluentDotEnvException
{
    /**
     * A key is missing.
     *
     * @param string $key The key that's missing.
     * @return self
     */
    public static function missingKey(string $key): self
    {
        return new self('Required key '.$key.' is missing');
    }

    /**
     * A key's value is empty.
     *
     * @param string $key The key that's empty.
     * @return self
     */
    public static function isEmpty(string $key): self
    {
        return new self($key.' is empty');
    }

    /**
     * Value is not an integer.
     *
     * @param string $key   The key whose value is invalid.
     * @param mixed  $value The incorrect value.
     * @return self
     */
    public static function notAnInteger(string $key, $value): self
    {
        return new self($key.' value "'.$value.'" is not an integer');
    }

    /**
     * Value is not a boolean.
     *
     * @param string $key   The key whose value is invalid.
     * @param mixed  $value The incorrect value.
     * @return self
     */
    public static function notABoolean(string $key, $value): self
    {
        return new self($key.' value "'.$value.'" is not a boolean');
    }

    /**
     * Value is not allowed from a set of predefined values.
     *
     * @param string  $key           The key whose value is invalid.
     * @param mixed   $value         The incorrect value.
     * @param mixed[] $allowedValues The allowed values.
     * @return self
     */
    public static function valueNotAllowed(string $key, $value, array $allowedValues): self
    {
        return new self(
            $key.' value "'.$value.'" is not in the allowed list: '
            .'"'.implode('", "', $allowedValues).'"'
        );
    }

    /**
     * A global validation callback closure decided the value is invalid.
     *
     * @param string $key   The key whose value is invalid.
     * @param mixed  $value The incorrect value.
     * @return self
     */
    public static function globalCallbackCheckFailed(string $key, $value): self
    {
        return new self($key.' value "'.$value.'" failed a global callback check');
    }

    /**
     * A validation callback closure decided the value is invalid.
     *
     * @param string $key   The key whose value is invalid.
     * @param mixed  $value The incorrect value.
     * @return self
     */
    public static function callbackCheckFailed(string $key, $value): self
    {
        return new self($key.' value "'.$value.'" failed a callback check');
    }

    /**
     * A regex check failed.
     *
     * @param string $key   The key whose value is invalid.
     * @param mixed  $value The incorrect value.
     * @param string $regex The regex being checked against.
     * @return self
     */
    public static function regexCheckFailed(string $key, $value, string $regex): self
    {
        return new self($key.' value "'.$value.'" did not match regex "'.$regex.'"');
    }
}
