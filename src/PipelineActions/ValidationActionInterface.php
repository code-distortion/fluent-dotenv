<?php

namespace CodeDistortion\FluentDotEnv\PipelineActions;

use CodeDistortion\FluentDotEnv\Exceptions\FluentDotEnvException;
use CodeDistortion\FluentDotEnv\Misc\ValueStore;

/**
 * Interface for ValidationActions.
 */
interface ValidationActionInterface
{
    /**
     * Add keys to the list.
     *
     * @param string[] $keys The keys to add.
     * @return static
     */
    public function add(array $keys);

    /**
     * Perform the action that this object does.
     *
     * @param ValueStore $values The value-store to apply the validation against.
     * @return static
     * @throws FluentDotEnvException When something is invalid.
     */
    public function apply(ValueStore $values);
}
