<?php

namespace CodeDistortion\FluentDotEnv\PipelineActions\Validation;

use CodeDistortion\FluentDotEnv\PipelineActions\AbstractValidationAction;
use CodeDistortion\FluentDotEnv\Exceptions\ValidationException;
use CodeDistortion\FluentDotEnv\Misc\ValueStore;

/**
 * Action to check that keys are present.
 */
class RequiredAction extends AbstractValidationAction
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
     * Make sure the required keys are present.
     *
     * @param ValueStore $values The value-store to apply the validation against.
     * @return static
     * @throws ValidationException When a required field is missing.
     */
    protected function applyToValueStore(ValueStore $values)
    {
        foreach ($this->keys as $key) {
            if (!$values->hasKey($key)) {
                throw ValidationException::missingKey($key);
            }
        }
        return $this;
    }
}
