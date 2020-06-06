<?php

namespace CodeDistortion\FluentDotEnv\PipelineActions;

use CodeDistortion\FluentDotEnv\Misc\ValueStore;

/**
 * PopulateAction base class.
 */
abstract class AbstractPopulationAction implements PopulateActionInterface
{
    /**
     * Should population be performed?
     *
     * @var boolean
     */
    protected $enabled = false;

    /**
     * When populating, should existing values be overridden?
     *
     * @var boolean
     */
    protected $overrideExisting = false;


    /**
     * Reset the set of things to apply.
     *
     * @return static
     */
    protected function reset()
    {
        $this->enabled = $this->overrideExisting = false;
        return $this;
    }

    /**
     * Turn population on.
     *
     * @param boolean $overrideExisting Should values override ones that already exist?.
     * @return static
     */
    public function enable(bool $overrideExisting = false)
    {
        $this->enabled = true;
        $this->overrideExisting = $overrideExisting;
        return $this;
    }

    /**
     * Perform the action that this object does - and reset afterwards.
     *
     * @param ValueStore $values The value-store to read from when populating.
     * @return static
     */
    public function apply(ValueStore $values)
    {
        if ($this->enabled) {
            $this->applyFromValueStore($values);
        }
        return $this->reset();
    }

    /**
     * Perform the action that this object does.
     *
     * @param ValueStore $values The value-store to read from when populating.
     * @return static
     */
    abstract protected function applyFromValueStore(ValueStore $values);
}
