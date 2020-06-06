<?php

namespace CodeDistortion\FluentDotEnv\PipelineActions;

use CodeDistortion\FluentDotEnv\Misc\ValueStore;

/**
 * FilterAction base class.
 */
abstract class AbstractFilterAction implements FilterActionInterface
{
    /**
     * Should filtering be performed?
     *
     * @var boolean
     */
    protected $enabled = false;

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
        $this->enabled = false;
        $this->keys = [];
        return $this;
    }

    /**
     * Add keys to the list.
     *
     * @param string[] $keys The keys to add.
     * @return static
     */
    public function add(array $keys)
    {
        $this->enabled = true;
        $this->keys = array_merge(
            $this->keys,
            array_values($keys)
        );
        return $this;
    }

    /**
     * Perform the action that this object does - and reset afterwards.
     *
     * @param ValueStore $values The value-store to apply the filtering to.
     * @return static
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
     * @param ValueStore $values The value-store to apply the filtering to.
     * @return static
     */
    abstract protected function applyToValueStore(ValueStore $values);
}
