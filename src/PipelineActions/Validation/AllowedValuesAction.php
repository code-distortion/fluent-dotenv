<?php

namespace CodeDistortion\FluentDotEnv\PipelineActions\Validation;

use CodeDistortion\FluentDotEnv\PipelineActions\AbstractValidationAction;
use CodeDistortion\FluentDotEnv\Exceptions\ValidationException;
use CodeDistortion\FluentDotEnv\Misc\ValueStore;

/**
 * Action to check that keys have allowed values.
 */
class AllowedValuesAction extends AbstractValidationAction
{
    /**
     * The keys to use.
     *
     * @var string[][]
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
     * @param string[][] $keys The keys to add.
     * @return static
     */
    protected function addKeys(array $keys)
    {
        foreach ($keys as $key => $allowedValues) {
            $this->keys[$key] = $this->keys[$key] ?? [];
            $this->keys[$key] = array_merge($this->keys[$key], array_values($allowedValues));
        }
        return $this;
    }

    /**
     * If the keys are present, make sure their values are allowed.
     *
     * @param ValueStore $values The value-store to apply the validation against.
     * @return static
     * @throws ValidationException When a value is not allowed.
     */
    protected function applyToValueStore(ValueStore $values)
    {
        foreach ($this->keys as $key => $allowedValues) {
            if (($values->hasKey($key)) && (!in_array($values->get($key), $allowedValues, true))) {
                throw ValidationException::valueNotAllowed($key, $values->get($key), $allowedValues);
            }
        }
        return $this;
    }
}
