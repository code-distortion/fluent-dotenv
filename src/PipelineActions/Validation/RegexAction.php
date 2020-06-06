<?php

namespace CodeDistortion\FluentDotEnv\PipelineActions\Validation;

use CodeDistortion\FluentDotEnv\PipelineActions\AbstractValidationAction;
use CodeDistortion\FluentDotEnv\Exceptions\ValidationException;
use CodeDistortion\FluentDotEnv\Misc\ValueStore;

/**
 * Action to check that keys' values pass regex/es.
 */
class RegexAction extends AbstractValidationAction
{
    /**
     * The keys to use.
     *
     * @var string[][]
     */
    private $keys = [];


    /**
     * Reset the set of things to apply.
     *
     * @return static
     */
    protected function reset()
    {
        $this->keys = [];
        return $this;
    }

    /**
     * Add keys to the list.
     *
     * @param string[][] $keys The keys to add.
     * @return static
     */
    protected function addKeys(array $keys)
    {
        foreach ($keys as $key => $regexes) {
            $this->keys[$key] = $this->keys[$key] ?? [];
            $this->keys[$key] = array_merge($this->keys[$key], $regexes);
        }
        return $this;
    }

    /**
     * Make sure the keys' regexes pass if they're present.
     *
     * @param ValueStore $values The value-store to apply the validation against.
     * @return static
     * @throws ValidationException When a value doesn't match a regex.
     */
    protected function applyToValueStore(ValueStore $values)
    {
        foreach ($this->keys as $key => $regexes) {
            if ($values->hasKey($key)) {
                foreach ($regexes as $regex) {
                    if (!preg_match($regex, $values->get($key))) {
                        throw ValidationException::regexCheckFailed($key, $values->get($key), $regex);
                    }
                }
            }
        }
        return $this;
    }
}
