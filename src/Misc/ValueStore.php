<?php

namespace CodeDistortion\FluentDotEnv\Misc;

/**
 * A store of values read from .env file/s.
 */
class ValueStore
{
    /**
     * The imported values.
     *
     * @var array<string, mixed>
     */
    private $values;

    /**
     * The imported values - before any filtering.
     *
     * @var array<string, mixed>
     */
    private $original;

    /**
     * Keys to "pick".
     *
     * @var string[]|null
     */
    private $pickKeys = null;


    /**
     * Constructor.
     *
     * @param array<string, mixed> $values The imported values.
     */
    public function __construct(array $values = [])
    {
        $this->values = $this->original = $values;
    }

    /**
     * Take the given ValueStore/s and merge their values in to this.
     *
     * @param ValueStore $valueStore The ValueStore to merge with this.
     * @return void
     */
    public function merge(ValueStore $valueStore)
    {
        foreach (func_get_args() as $valueStore) {
            /** @var ValueStore $valueStore */
            $this->original = array_merge(
                $this->original,
                $valueStore->original
            );
        }
        $this->recalculateValues();
    }

    /**
     * Add the given keys to the list of keys to "pick".
     *
     * @param string[] $keys The keys to pick.
     * @return void
     */
    public function pick(array $keys)
    {
        $this->pickKeys = $this->pickKeys ?? [];
        $this->pickKeys = array_merge($this->pickKeys, array_values($keys));
        $this->recalculateValues();
    }

    /**
     * Recalculate the "values" based on the original-values and the keys to "pick".
     *
     * @return void
     */
    public function recalculateValues()
    {
        if (is_null($this->pickKeys)) {
            $this->values = $this->original;
            return;
        }

        $this->values = [];
        foreach ($this->pickKeys as $key) {
            if (array_key_exists($key, $this->original)) {
                $this->values[$key] = $this->original[$key];
            }
        }
    }

    /**
     * Return all of the stored values.
     *
     * @return array<string, mixed>
     */
    public function all()
    {
        return $this->values;
    }

    /**
     * Get a stored value.
     *
     * @param string $key The key to get the value for.
     * @return mixed
     */
    public function get(string $key)
    {
        return ($this->hasKey($key) ? $this->values[$key] : null);
    }

    /**
     * Check if a key exists.
     *
     * @param string $key The key to check.
     * @return boolean
     */
    public function hasKey(string $key): bool
    {
        return array_key_exists($key, $this->values);
    }

    /**
     * Forget a particular key.
     *
     * @param string $key The key to forget.
     * @return void
     */
    public function forgetKey(string $key)
    {
        unset($this->original[$key], $this->values[$key]);
    }
}
