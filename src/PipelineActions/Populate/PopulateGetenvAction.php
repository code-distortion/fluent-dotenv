<?php

namespace CodeDistortion\FluentDotEnv\PipelineActions\Populate;

use CodeDistortion\FluentDotEnv\PipelineActions\AbstractPopulationAction;
use CodeDistortion\FluentDotEnv\Misc\GetenvSupport;
use CodeDistortion\FluentDotEnv\Misc\ValueStore;

/**
 * Action to populate getenv() with values.
 */
class PopulateGetenvAction extends AbstractPopulationAction
{

    /**
     * Populate the getenv() function with values from the value-store.
     *
     * @param ValueStore $values The value-store to read from when populating.
     * @return static
     */
    protected function applyFromValueStore(ValueStore $values)
    {
        $getenvValues = GetenvSupport::getAllGetenvVariables();

        foreach ($values->all() as $key => $value) {
            if (($this->overrideExisting) || (!array_key_exists($key, $getenvValues))) {
                $getenvValues[$key] = $value;
            }
        }

        GetenvSupport::replaceAllGetenvVariables($getenvValues);

        return $this;
    }
}
