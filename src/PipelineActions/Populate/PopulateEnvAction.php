<?php

namespace CodeDistortion\FluentDotEnv\PipelineActions\Populate;

use CodeDistortion\FluentDotEnv\PipelineActions\AbstractPopulationAction;
use CodeDistortion\FluentDotEnv\Misc\ValueStore;

/**
 * Action to populate $_ENV with values.
 */
class PopulateEnvAction extends AbstractPopulationAction
{
    /**
     * Populate $_ENV with values from the value-store.
     *
     * @param ValueStore $values The value-store to read from when populating.
     * @return static
     */
    protected function applyFromValueStore(ValueStore $values)
    {
        foreach ($values->all() as $key => $value) {
            if (($this->overrideExisting) || (!array_key_exists($key, $_ENV))) {
                $_ENV[$key] = $value;
            }
        }
        return $this;
    }
}
