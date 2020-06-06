<?php

namespace CodeDistortion\FluentDotEnv\PipelineActions\Validation;

use CodeDistortion\FluentDotEnv\PipelineActions\AbstractValidationAction;
use CodeDistortion\FluentDotEnv\Exceptions\ValidationException;
use CodeDistortion\FluentDotEnv\Misc\ValueStore;

/**
 * Action to validate specific key values via a callback.
 */
class CallbackAction extends AbstractValidationAction
{
    /**
     * Callbacks to apply to specific values.
     *
     * @var callable[][]
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
     * @param callable[][] $callbacks The callbacks to add.
     * @return static
     */
    protected function addKeys(array $callbacks)
    {
        foreach ($callbacks as $key => $someCallbacks) {
            $this->callbacks[$key] = $this->callbacks[$key] ?? [];
            $this->callbacks[$key] = array_merge($this->callbacks[$key], $someCallbacks);
        }
        return $this;
    }

    /**
     * Make sure the keys' callbacks pass if they're present.
     *
     * @param ValueStore $values The value-store to apply the validation against.
     * @return static
     * @throws ValidationException When a value isn't valid according to a callback.
     */
    protected function applyToValueStore(ValueStore $values)
    {
        foreach ($this->callbacks as $key => $someCallbacks) {
            if ($values->hasKey($key)) {
                foreach ($someCallbacks as $callback) {
                    if (!$callback($key, $values->get($key))) {
                        throw ValidationException::callbackCheckFailed($key, $values->get($key));
                    }
                }
            }
        }
        return $this;
    }
}
