<?php

namespace CodeDistortion\FluentDotEnv;

use CodeDistortion\FluentDotEnv\PipelineActions\Filter\IgnoreAction;
use CodeDistortion\FluentDotEnv\PipelineActions\Filter\PickAction;
use CodeDistortion\FluentDotEnv\PipelineActions\FilterActionInterface;
use CodeDistortion\FluentDotEnv\PipelineActions\Populate\PopulateEnvAction;
use CodeDistortion\FluentDotEnv\PipelineActions\Populate\PopulateServerAction;
use CodeDistortion\FluentDotEnv\PipelineActions\PopulateActionInterface;
use CodeDistortion\FluentDotEnv\PipelineActions\Validation\AllowedValuesAction;
use CodeDistortion\FluentDotEnv\PipelineActions\Validation\BooleanAction;
use CodeDistortion\FluentDotEnv\PipelineActions\Validation\CallbackAction;
use CodeDistortion\FluentDotEnv\PipelineActions\Validation\CallbackGlobalAction;
use CodeDistortion\FluentDotEnv\PipelineActions\Validation\IntegerAction;
use CodeDistortion\FluentDotEnv\PipelineActions\Validation\NotEmptyAction;
use CodeDistortion\FluentDotEnv\PipelineActions\Validation\RegexAction;
use CodeDistortion\FluentDotEnv\PipelineActions\Validation\RequiredAction;
use CodeDistortion\FluentDotEnv\PipelineActions\ValidationActionInterface;
use CodeDistortion\FluentDotEnv\DotEnvAdapters\DotEnvAdapterPicker;
use CodeDistortion\FluentDotEnv\Exceptions\AlreadyLoadedException;
use CodeDistortion\FluentDotEnv\Exceptions\DependencyException;
use CodeDistortion\FluentDotEnv\Exceptions\InvalidPathException;
use CodeDistortion\FluentDotEnv\Misc\ValueStore;

//use CodeDistortion\FluentDotEnv\Actions\Populate\PopulateGetenvAction;

/**
 * A DotEnv reader with a fluent interface.
 */
class FluentDotEnv
{
    /**
     * The order to try loading adapters in.
     *
     * @var string[]
     */
    private $adapterOrder = ['vlucas'];

    /**
     * The store for imported values.
     *
     * @var ValueStore
     */
    private $valueStore;

    /**
     * Has a .env file been loaded yet?.
     *
     * @var boolean
     */
    private $isLoaded = false;


    /**
     * Only lets allowed values through.
     *
     * @var PickAction
     */
    private $pickAction;

    /**
     * Ignores particular values.
     *
     * @var IgnoreAction
     */
    private $ignoreAction;


    /**
     * Makes sure values aren't missing.
     *
     * @var RequiredAction
     */
    private $requiredAction;

    /**
     * Makes sure values aren't empty.
     *
     * @var NotEmptyAction
     */
    private $notEmptyAction;

    /**
     * Makes sure values are integers.
     *
     * @var IntegerAction
     */
    private $integerAction;

    /**
     * Makes sure values are booleans.
     *
     * @var BooleanAction
     */
    private $booleanAction;

    /**
     * Makes sure values are in allowed-values lists.
     *
     * @var AllowedValuesAction
     */
    private $allowedValuesAction;

    /**
     * Makes sure values match regexes.
     *
     * @var RegexAction
     */
    private $regexAction;

    /**
     * Validate all values against callbacks.
     *
     * @var CallbackGlobalAction
     */
    private $callbackGlobalAction;

    /**
     * Validate values against callbacks.
     *
     * @var CallbackAction
     */
    private $callbackAction;


//    /**
//     * Populates the getenv() function with new values.
//     *
//     * @var PopulateGetenvAction
//     */
//    private $populateGetenvAction;

    /**
     * Populates the $_ENV superglobal with new values.
     *
     * @var PopulateEnvAction
     */
    private $populateEnvAction;

    /**
     * Populates the $_SERVER superglobal with new values.
     *
     * @var PopulateServerAction
     */
    private $PopulateServerAction;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->valueStore = new ValueStore();

        // pre-import actions
        $this->pickAction = new PickAction();
        $this->ignoreAction = new IgnoreAction();

        // validation actions
        $this->requiredAction = new RequiredAction();
        $this->notEmptyAction = new NotEmptyAction();
        $this->integerAction = new IntegerAction();
        $this->booleanAction = new BooleanAction();
        $this->allowedValuesAction = new AllowedValuesAction();
        $this->regexAction = new RegexAction();
        $this->callbackGlobalAction = new CallbackGlobalAction();
        $this->callbackAction = new CallbackAction();

        // population actions
//        $this->populateGetenvAction = new populateGetenvAction();
        $this->populateEnvAction = new PopulateEnvAction();
        $this->PopulateServerAction = new PopulateServerAction();
    }

    /**
     * An alternative way to instantiate this class.
     *
     * @return self
     */
    public static function new()
    {
        return new self();
    }



    /**
     * Use the symfony/dotenv adapter to load values from .env files.
     *
     * @return static
     */
    public function useSymfonyDotEnv()
    {
        $this->adapterOrder = ['symfony'];
        return $this;
    }

    /**
     * Use the vlucas/phpdotenv adapter to load values from .env files.
     *
     * @return static
     */
    public function useVlucasPhpDotEnv()
    {
        $this->adapterOrder = ['vlucas'];
        return $this;
    }


    /**
     * Load the values from the given .env file, apply filtering, validation and populate $_ENV and $_SERVER
     * where necessary.
     *
     * @param string|string[] $path The path to the .env file.
     * @return static
     * @throws DependencyException  When a .env loading package cannot be found (eg. vlucas/phpdotenv).
     * @throws InvalidPathException When the .env file could not be loaded.
     * @throws AlreadyLoadedException When .env values have already been loaded.
     */
    public function load($path)
    {
        $paths = $this->resolveKeys(func_get_args());
        return $this->loadFiles($paths, false);
    }

    /**
     * Load the values from the given .env file, apply filtering, validation and populate $_ENV and $_SERVER
     * where necessary. No exception will be thrown a path cannot be used.
     *
     * @param string|string[] $path The path to the .env file.
     * @return static
     * @throws DependencyException When a .env loading package cannot be found (eg. vlucas/phpdotenv).
     * @throws AlreadyLoadedException When .env values have already been loaded.
     */
    public function safeLoad($path)
    {
        $paths = $this->resolveKeys(func_get_args());
        return $this->loadFiles($paths, true);
    }

    /**
     * Actually load from the given .env files and process their values.
     *
     * @param string[] $paths  The .env paths to load.
     * @param boolean  $safely Should the exception be absorbed when a .env file could not be loaded?.
     * @return static
     * @throws DependencyException    When a .env loading package cannot be found (eg. vlucas/phpdotenv).
     * @throws InvalidPathException   When the .env file could not be loaded.
     * @throws AlreadyLoadedException When .env values have already been loaded.
     */
    private function loadFiles(array $paths, bool $safely)
    {
        if ($this->isLoaded) {
            throw AlreadyLoadedException::alreadyLoaded();
        }

        $dotEnvAdapter = DotEnvAdapterPicker::pickAdapter($this->adapterOrder);
        foreach ($paths as $path) {
            try {
                $valueStore = $dotEnvAdapter->import($path);
                $this->valueStore->merge($valueStore);
            } catch (InvalidPathException $e) {
                if (!$safely) {
                    throw($e);
                }
            }
        }

        $this->isLoaded = true;
        $this->applyActions();
        return $this;
    }

    /**
     * Apply the desired actions to the current value-store.
     *
     * @return void
     */
    private function applyActions()
    {
        $filterActions = [
            $this->pickAction,
            $this->ignoreAction,
        ];
        $validationActions = [
            $this->requiredAction,
            $this->notEmptyAction,
            $this->integerAction,
            $this->booleanAction,
            $this->allowedValuesAction,
            $this->regexAction,
            $this->callbackGlobalAction,
            $this->callbackAction,
        ];
        $populateActions = [
//            $this->populateGetenvAction,
            $this->populateEnvAction,
            $this->PopulateServerAction,
        ];

        foreach (array_merge($filterActions, $validationActions, $populateActions) as $action) {
            /** @var FilterActionInterface|ValidationActionInterface|PopulateActionInterface $action */
            $action->apply($this->valueStore);
        }
    }


    /**
     * Add to the list of keys to import (others are ignored).
     *
     * @param string|string[] $keys The keys to allow.
     * @return static
     */
    public function pick($keys)
    {
        return $this->filter($this->pickAction, $this->resolveKeys(func_get_args()));
    }

    /**
     * Add to the list of keys to ignore.
     *
     * @param string|string[] $keys The keys to ignore.
     * @return static
     */
    public function ignore($keys)
    {
        return $this->filter($this->ignoreAction, $this->resolveKeys(func_get_args()));
    }


    /**
     * Add to the list of keys required keys (an exception is thrown when missing).
     *
     * @param string|string[] $keys The keys to require.
     * @return static
     */
    public function required($keys)
    {
        return $this->validate($this->requiredAction, $this->resolveKeys(func_get_args()));
    }

    /**
     * Add to the list of keys that cannot be empty when present (an exception is thrown when they are).
     *
     * @param string|string[] $keys The keys that cannot be empty.
     * @return static
     */
    public function notEmpty($keys)
    {
        return $this->validate($this->notEmptyAction, $this->resolveKeys(func_get_args()));
    }

    /**
     * Add to the list of keys that must be integers when present (an exception is thrown when they aren't).
     *
     * @param string|string[] $keys The keys that must have integer values.
     * @return static
     */
    public function integer($keys)
    {
        return $this->validate($this->integerAction, $this->resolveKeys(func_get_args()));
    }

    /**
     * Add to the list of keys that must be booleans when present (an exception is thrown when they aren't).
     *
     * @param string|string[] $keys The keys that must have boolean values.
     * @return static
     */
    public function boolean($keys)
    {
        return $this->validate($this->booleanAction, $this->resolveKeys(func_get_args()));
    }

    /**
     * Specify keys and their allowed values (an exception is thrown when other values are found).
     *
     * @param string|string[] $key           The key to specify values for.
     * @param string[]        $allowedValues The possible values the key can have.
     * @return static
     */
    public function allowedValues($key, array $allowedValues = [])
    {
        return $this->validate($this->allowedValuesAction, $this->resolveKeyValuePairs($key, $allowedValues));
    }

    /**
     * Specify keys and regexes they need to match (an exception is thrown when they don't).
     *
     * @param string|string[] $key   The key to specify regexes for.
     * @param string          $regex The regex to apply.
     * @return static
     */
    public function regex($key, string $regex = '')
    {
        return $this->validate($this->regexAction, $this->resolveKeyValuePairs($key, $regex, true));
    }

    /**
     * Specify keys and closures they need to checked with (an exception is thrown when the closure returns false).
     *
     * @param callable|callable[]|string|string[] $key     The key to specify closures for.
     * @param callable                            $closure The closure to apply.
     * @return static
     */
    public function callback($key, callable $closure = null)
    {
        return (is_callable($key))
            ? $this->validate($this->callbackGlobalAction, [$key])
            : $this->validate($this->callbackAction, $this->resolveKeyValuePairs($key, $closure, true));
    }


//    /**
//     * Turn the setting to add imported values to the getenv() method on.
//     *
//     * @param boolean $overrideExisting Should new values override old ones if they already existed?.
//     * @return static
//     */
//    public function populateGetenv(bool $overrideExisting = false)
//    {
//        return $this->populate($this->populateGetenvAction, $overrideExisting);
//    }

    /**
     * Turn the setting to add imported values to the $_ENV superglobal on.
     *
     * @param boolean $overrideExisting Should new values override old ones if they already existed?.
     * @return static
     */
    public function populateEnv(bool $overrideExisting = false)
    {
        return $this->populate($this->populateEnvAction, $overrideExisting);
    }

    /**
     * Turn the setting to add imported values to the $_SERVER superglobal on.
     *
     * @param boolean $overrideExisting Should new values override old ones if they already existed?.
     * @return static
     */
    public function populateServer(bool $overrideExisting = false)
    {
        return $this->populate($this->PopulateServerAction, $overrideExisting);
    }


    /**
     * Check if .env file/s have been loaded.
     *
     * @return boolean
     */
    private function isLoaded(): bool
    {
        return $this->isLoaded;
    }

    /**
     * Get all the imported values.
     *
     * @return mixed[]
     */
    public function all(): array
    {
        return $this->valueStore->all();
    }

    /**
     * Get a particular imported value.
     *
     * @param string|string[] $key The key to get the value for.
     * @return mixed
     */
    public function get($key)
    {
        return $this->retrieveValues(
            $this->resolveKeys(func_get_args()),
            null,
            ((is_array($key)) || (count(func_get_args()) > 1))
        );
    }

    /**
     * Return the value, cast to a boolean.
     *
     * (Will return null if it isn't a "boolean string").
     *
     * @param string|string[] $key The key to get the value for.
     * @return boolean|boolean[]|null
     */
    public function castBoolean($key)
    {
        return $this->retrieveValues(
            $this->resolveKeys(func_get_args()),
            'castABoolean',
            ((is_array($key)) || (count(func_get_args()) > 1))
        );
    }

    /**
     * Return the value, cast to an integer.
     *
     * (Will return null if it isn't an integer string).
     *
     * @param string|string[] $key The key to get the value for.
     * @return integer|integer[]|null
     */
    public function castInteger($key)
    {
        return $this->retrieveValues(
            $this->resolveKeys(func_get_args()),
            'castAnInteger',
            ((is_array($key)) || (count(func_get_args()) > 1))
        );
    }

    /**
     * Take the given keys, get their values, and cast them if necessary.
     *
     * @param string[]    $keys       The keys to get values for.
     * @param string|null $castMethod The method to cast the values with.
     * @param boolean     $needsArray Whether to return as an array or not.
     * @return array|string|integer|boolean|null
     */
    private function retrieveValues(array $keys, $castMethod, bool $needsArray)
    {
        $values = [];
        foreach ($keys as $key) {
            $value = $this->valueStore->get($key);
            $values[$key] = ($castMethod
                ? $this->$castMethod($value)
                : $value
            );
        }
        return ($needsArray ? $values : reset($values));
    }

    /**
     * Return the value, cast as a boolean.
     *
     * (Will return null if it isn't a "boolean string").
     *
     * @param string $value The value to cast.
     * @return boolean|null
     */
    private function castABoolean(string $value)
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    /**
     * Return the value, cast as an integer.
     *
     * (Will return null if it isn't an integer string).
     *
     * @param string $value The value to cast.
     * @return integer|null
     */
    private function castAnInteger(string $value)
    {
        if (($value === null) || ($value === '')) {
            return null;
        }

        $isNegative = (mb_strpos($value, '-') === 0);
        if ($isNegative) {
            $value = mb_substr($value, 1);
        }
        if (!ctype_digit($value)) {
            return null;
        }

        return ($isNegative ? -(int) $value : (int) $value);
    }


    /**
     * Generate the set of keys from method input.
     *
     * @param mixed[] $args The args to look through for values.
     * @return string[]
     */
    private function resolveKeys(array $args): array
    {
        $newValues = [];
        foreach ($args as $arg) {
            if (!is_null($arg)) {
                $values = (is_array($arg) ? $arg : [$arg]);
                $newValues = array_merge($newValues, $values);
            }
        }
        return array_unique($newValues);
    }

    /**
     * Generate key-value pairs from method input.
     *
     * @param callable|callable[]|string|string[]|null[] $key              The key that was passed (may be an array to
     *                                                                     look in to).
     * @param mixed                                      $thing            The thing that was passed.
     * @param boolean                                    $makeValuesArrays Put the values into an array.
     * @return mixed[]
     */
    private function resolveKeyValuePairs($key, $thing, bool $makeValuesArrays = false): array
    {
        $keys = (is_array($key) ? $key : [$key]);
        $keyThings = [];
        foreach ($keys as $index => $value) {
            if (is_int($index)) {
                if (!is_null($thing)) {
                    $keyThings[$value] = $thing;
                }
            } else {
                if (!is_null($value)) {
                    $keyThings[$index] = $value;
                }
            }
        }
        if ($makeValuesArrays) {
            foreach ($keyThings as $key => $thing) {
                $keyThings[$key] = [$thing];
            }
        }
        return $keyThings;
    }

    /**
     * Add to a filter action.
     *
     * @param FilterActionInterface $filterAction The action to add to.
     * @param mixed[]               $values       The things to add.
     * @return static
     * @throws Exceptions\FluentDotEnvException When something is invalid.
     */
    private function filter(FilterActionInterface $filterAction, array $values)
    {
        $this->isLoaded()
            ? $filterAction->add($values)->apply($this->valueStore)
            : $filterAction->add($values);
        return $this;
    }

    /**
     * Add to a validation action.
     *
     * @param ValidationActionInterface $validationAction The action to add to.
     * @param mixed[]                   $values           The things to add.
     * @return static
     * @throws Exceptions\FluentDotEnvException When something is invalid.
     */
    private function validate(ValidationActionInterface $validationAction, array $values)
    {
        $this->isLoaded()
            ? $validationAction->add($values)->apply($this->valueStore)
            : $validationAction->add($values);
        return $this;
    }

    /**
     * Turn the setting to add imported values to the $_SERVER superglobal on.
     *
     * @param PopulateActionInterface $populateAction   The action to add to.
     * @param boolean                 $overrideExisting Should new values override old ones if they already existed?.
     * @return static
     */
    private function populate(PopulateActionInterface $populateAction, bool $overrideExisting)
    {
        $this->isLoaded()
            ? $populateAction->enable($overrideExisting)->apply($this->valueStore)
            : $populateAction->enable($overrideExisting);
        return $this;
    }
}
