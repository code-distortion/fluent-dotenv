<?php

namespace CodeDistortion\FluentDotEnv\PipelineActions;

use CodeDistortion\FluentDotEnv\Misc\ValueStore;

/**
 * Interface for PopulateActions.
 */
interface PopulateActionInterface
{
    /**
     * Turn population on.
     *
     * @param boolean $overrideExisting Should values override ones that already exist?.
     * @return static
     */
    public function enable(bool $overrideExisting = false);

    /**
     * Perform the action that this object does - and reset afterwards.
     *
     * @param ValueStore $values The value-store to read from when populating.
     * @return static
     */
    public function apply(ValueStore $values);
}
