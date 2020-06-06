<?php

namespace CodeDistortion\FluentDotEnv\PipelineActions\Validation;

use CodeDistortion\FluentDotEnv\PipelineActions\AbstractValidationAction;
use CodeDistortion\FluentDotEnv\Exceptions\ValidationException;
use CodeDistortion\FluentDotEnv\Misc\ValueStore;

/**
 * Action to validate ALL key values via a callback.
 */
class CallbackGlobalAction extends AbstractValidationAction
{
    /**
     * Callbacks to apply to every value.
     *
     * @var callable[]
     */
    private $callbacks = [];


    /**
     * Reset the set of things to apply.
     *
     * @return static
     */
    protected function reset()
    {
        $this->callbacks = [];
        return $this;
    }

    /**
     * Add callbacks to the list.
     *
     * @param callable[] $callbacks The callbacks to add.
     * @return static
     */
    protected function addKeys(array $callbacks)
    {
        $this->callbacks = array_merge(
            $this->callbacks,
            $callbacks
        );
        return $this;
    }

    /**
     * Make sure all keys pass the global-callbacks.
     *
     * @param ValueStore $values The value-store to apply the validation against.
     * @return static
     * @throws ValidationException When a value isn't valid according to a callback.
     */
    protected function applyToValueStore(ValueStore $values)
    {
        if (count($this->callbacks)) {
            foreach ($values->all() as $key => $value) {
                foreach ($this->callbacks as $callback) {
                    if (!$callback($key, $values->get($key))) {
                        throw ValidationException::globalCallbackCheckFailed($key, $values->get($key));
                    }
                }
            }
        }
        return $this;
    }
}
