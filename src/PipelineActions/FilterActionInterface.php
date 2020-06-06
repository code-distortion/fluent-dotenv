<?php

namespace CodeDistortion\FluentDotEnv\PipelineActions;

use CodeDistortion\FluentDotEnv\Misc\ValueStore;

/**
 * Interface for FilterActions.
 */
interface FilterActionInterface
{
    /**
     * Add keys to the list.
     *
     * @param string[] $keys The keys to add.
     * @return static
     */
    public function add(array $keys);

    /**
     * Perform the action that this object does - and reset afterwards.
     *
     * @param ValueStore $values The value-store to apply the filtering to.
     * @return static
     */
    public function apply(ValueStore $values);
}
