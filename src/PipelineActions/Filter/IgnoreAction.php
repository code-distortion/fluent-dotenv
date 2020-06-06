<?php

namespace CodeDistortion\FluentDotEnv\PipelineActions\Filter;

use CodeDistortion\FluentDotEnv\PipelineActions\AbstractFilterAction;
use CodeDistortion\FluentDotEnv\Misc\ValueStore;

/**
 * Action to ignore .env values.
 */
class IgnoreAction extends AbstractFilterAction
{
    /**
     * Remove the unwanted keys from the value-store.
     *
     * @param ValueStore $values The value-store to apply the filtering to.
     * @return static
     */
    protected function applyToValueStore(ValueStore $values)
    {
        foreach ($values->all() as $key => $value) {
            if (in_array($key, $this->keys)) {
                $values->forgetKey($key);
            }
        }
        return $this;
    }
}
