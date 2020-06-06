<?php

namespace CodeDistortion\FluentDotEnv\PipelineActions\Filter;

use CodeDistortion\FluentDotEnv\PipelineActions\AbstractFilterAction;
use CodeDistortion\FluentDotEnv\Misc\ValueStore;

/**
 * Action to pick .env values to keep.
 */
class PickAction extends AbstractFilterAction
{
    /**
     * Remove the unwanted keys from the value-store.
     *
     * @param ValueStore $values The value-store to apply the filtering to.
     * @return static
     */
    protected function applyToValueStore(ValueStore $values)
    {
        $values->pick($this->keys);
        return $this;
    }
}
