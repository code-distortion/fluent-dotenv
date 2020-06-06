<?php

namespace CodeDistortion\FluentDotEnv\PipelineActions\Validation;

use CodeDistortion\FluentDotEnv\PipelineActions\AbstractValidationAction;
use CodeDistortion\FluentDotEnv\Exceptions\ValidationException;
use CodeDistortion\FluentDotEnv\Misc\ValueStore;

/**
 * Action to check that keys have non-empty values.
 */
class NotEmptyAction extends AbstractValidationAction
{
    /**
     * The keys to use.
     *
     * @var string[]
     */
    protected $keys = [];


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
     * @param string[] $keys The keys to add.
     * @return static
     */
    protected function addKeys(array $keys)
    {
        $this->keys = array_merge(
            $this->keys,
            array_values($keys)
        );
        return $this;
    }

    /**
     * Make sure the keys aren't empty if they're present.
     *
     * @param ValueStore $values The value-store to apply the validation against.
     * @return static
     * @throws ValidationException When a value is empty.
     */
    protected function applyToValueStore(ValueStore $values)
    {
        foreach ($this->keys as $key) {
            if (($values->hasKey($key)) && ($this->isEmpty($values->get($key)))) {
                throw ValidationException::isEmpty($key);
            }
        }
        return $this;
    }

    /**
     * Check if the given value is "empty".
     *
     * @param mixed $value The value to check.
     * @return boolean
     * @see \Dotenv\Validator::notEmpty
     */
    private function isEmpty($value): bool
    {
        return mb_strlen(trim($value)) == 0;
    }
}
