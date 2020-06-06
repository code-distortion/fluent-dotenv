<?php

namespace CodeDistortion\FluentDotEnv\PipelineActions\Populate;

use CodeDistortion\FluentDotEnv\PipelineActions\AbstractPopulationAction;
use CodeDistortion\FluentDotEnv\Misc\ValueStore;

/**
 * Action to populate $_SERVER with values.
 */
class PopulateServerAction extends AbstractPopulationAction
{
    /**
     * Populate $_SERVER with values from the value-store.
     *
     * @param ValueStore $values The value-store to read from when populating.
     * @return static
     */
    protected function applyFromValueStore(ValueStore $values)
    {
        foreach ($values->all() as $key => $value) {
            if (($this->overrideExisting) || (!array_key_exists($key, $_SERVER))) {
                $_SERVER[$key] = $value;
            }
        }
        return $this;
    }
}
