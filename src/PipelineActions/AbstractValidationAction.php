<?php

namespace CodeDistortion\FluentDotEnv\PipelineActions;

use CodeDistortion\FluentDotEnv\Exceptions\FluentDotEnvException;
use CodeDistortion\FluentDotEnv\Misc\ValueStore;

/**
 * ValidationAction base class.
 */
abstract class AbstractValidationAction implements ValidationActionInterface
{
    /**
     * Should filtering be performed?
     *
     * @var boolean
     */
    protected $enabled = false;

    /**
     * Reset the set of things to apply.
     *
     * @return static
     */
    abstract protected function reset();

    /**
     * Add keys to the list.
     *
     * @param string[] $keys The keys to add.
     * @return static
     */
    public function add(array $keys)
    {
        $this->enabled = true;
        return $this->addKeys($keys);
    }

    /**
     * Add keys to the list.
     *
     * @param string[] $keys The keys to add.
     * @return static
     */
    abstract protected function addKeys(array $keys);

    /**
     * Perform the action that this object does - and reset afterwards.
     *
     * @param ValueStore $values The value-store to apply the validation against.
     * @return static
     * @throws FluentDotEnvException When something is invalid.
     */
    public function apply(ValueStore $values)
    {
        if ($this->enabled) {
            $this->applyToValueStore($values);
        }
        return $this->reset();
    }

    /**
     * Perform the action that this object does.
     *
     * @param ValueStore $values The value-store to apply the validation against.
     * @return static
     * @throws FluentDotEnvException When something is invalid.
     */
    abstract protected function applyToValueStore(ValueStore $values);
}
