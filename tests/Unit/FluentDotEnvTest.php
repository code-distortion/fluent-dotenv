<?php

namespace CodeDistortion\FluentDotEnv\Tests\Unit;

use CodeDistortion\FluentDotEnv\Exceptions\AlreadyLoadedException;
use CodeDistortion\FluentDotEnv\Exceptions\InvalidPathException;
use CodeDistortion\FluentDotEnv\Exceptions\ValidationException;
use CodeDistortion\FluentDotEnv\FluentDotEnv;
use CodeDistortion\FluentDotEnv\Tests\PHPUnitTestCase;

/**
 * Test the ConfigDTO class
 *
 * @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
class FluentDotEnvTest extends PHPUnitTestCase
{
    /**
     * This method is called before each test.
     *
     * This isn't inside setUp() because the signature needs to match across php versions.
     *
     * @return void
     */
    protected function customSetUp()
    {
        $_ENV = $_SERVER = [
            'UNTOUCHED_KEY' => 'untouched-value',
            'INITIAL_KEY' => 'initial-value',
        ];
        putenv('UNTOUCHED_KEY=untouched-value');
        putenv('INITIAL_KEY=initial-value');
        putenv('NEW_KEY'); // i.e. remove
        putenv('EMPTY_KEY'); // i.e. remove
        putenv('INTEGER_KEY'); // i.e. remove
        putenv('BOOLEAN_KEY'); // i.e. remove
    }

    /**
     * Create a new FluentDotEnv object (and get it to use the symfony/dotenv package to load .env files if it's
     * available).
     *
     * @return FluentDotEnv
     */
    protected static function newFluentDotEnv()
    {
        $fDotEnv = FluentDotEnv::new();
        if (file_exists(__DIR__ . '/../../vendor/symfony/dotenv')) {
            $fDotEnv->useSymfonyDotEnv();
        }
        return $fDotEnv;
    }

    /**
     * Generate data for the can_import_properly test below.
     *
     * @return array[]
     */
    public static function CanImportProperlyDataProvider(): array
    {
        $notImported = [
            'UNTOUCHED_KEY' => 'untouched-value',
            'INITIAL_KEY' => 'initial-value',
        ];
        $notOverridden = [
            'UNTOUCHED_KEY' => 'untouched-value',
            'INITIAL_KEY' => 'initial-value',
            'NEW_KEY' => 'new-value1',
            'EMPTY_KEY' => '',
            'INTEGER_KEY' => '5',
            'BOOLEAN_KEY' => 'true',
        ];
        $overridden = [
            'UNTOUCHED_KEY' => 'untouched-value',
            'INITIAL_KEY' => 'override-value',
            'NEW_KEY' => 'new-value1',
            'EMPTY_KEY' => '',
            'INTEGER_KEY' => '5',
            'BOOLEAN_KEY' => 'true',
        ];
        $imported = [
            'INITIAL_KEY' => 'override-value',
            'NEW_KEY' => 'new-value1',
            'EMPTY_KEY' => '',
            'INTEGER_KEY' => '5',
            'BOOLEAN_KEY' => 'true',
        ];

        $noValues = [];
        $rawNewKeyOnly = [
            'NEW_KEY' => 'new-value1',
        ];
        $rawIgnoredNewKey = [
            'INITIAL_KEY' => 'override-value',
            'EMPTY_KEY' => '',
            'INTEGER_KEY' => '5',
            'BOOLEAN_KEY' => 'true',
        ];

        $validCallback = function (string $key, $value) {
            return true;
        };
        $invalidCallback = function (string $key, $value) {
            return false;
        };

        $default = [
            'useSafeLoad' => false,
            'envFilename' => '.env',
            'pick' => null,
            'ignore' => null,
            'required' => null,
            'notEmpty' => null,
            'integer' => null,
            'boolean' => null,
            'allowedValues' => null,
            'regex' => null,
            'callback' => null,
//            'updateGetenv' => null,
            'updateEnv' => null,
            'updateServer' => null,
            'override' => null,
            'expectedValues' => $imported,
//            'expectedGetenv' => $notImported,
            'expectedEnv' => $notImported,
            'expectedServer' => $notImported,
            'expectedException' => null,
        ];

        $combinations = [
            'default' => [],

            // test filenames
            'filename 1' => [
                'envFilename' => '.env',
            ],
            'filename 2' => [
                'envFilename' => 'missing.env',
                'expectedException' => InvalidPathException::class,
            ],

            // safeload
            'safeLoad 1' => [
                'useSafeLoad' => true,
                'envFilename' => '.env',
            ],
            'safeLoad 2' => [
                'useSafeLoad' => true,
                'envFilename' => 'missing.env',
                'expectedValues' => $noValues,
            ],

            // test pick
            'pick 1' => [
                'pick' => [],
                'expectedValues' => [],
            ],
            'pick 2' => [
                'pick' => 'NEW_KEY', // as a string
                'expectedValues' => $rawNewKeyOnly,
            ],
            'pick 3' => [
                'pick' => ['NEW_KEY'],
                'expectedValues' => $rawNewKeyOnly,
            ],

            // test ignore
            'ignore 1' => [
                'ignore' => [],
                'expectedValues' => $imported,
            ],
            'ignore 2' => [
                'ignore' => 'NEW_KEY', // as a string
                'expectedValues' => $rawIgnoredNewKey,
            ],
            'ignore 3' => [
                'ignore' => ['NEW_KEY'],
                'expectedValues' => $rawIgnoredNewKey,
            ],

            // test required
            'required 1' => [
                'required' => [],
                'expectedValues' => $imported,
            ],
            'required 2' => [
                'required' => 'EMPTY_KEY', // as a string
                'expectedValues' => $imported,
            ],
            'required 3' => [
                'required' => ['EMPTY_KEY'],
                'expectedValues' => $imported,
            ],
            'required 4' => [
                'required' => 'NEW3',
                'expectedException' => ValidationException::class,
            ],

            // test not-empty
            'not-empty 1' => [
                'notEmpty' => [],
                'expectedValues' => $imported,
            ],
            'not-empty 2' => [
                'notEmpty' => 'NEW_KEY', // as a string
                'expectedValues' => $imported,
            ],
            'not-empty 3' => [
                'notEmpty' => ['NEW_KEY'],
                'expectedValues' => $imported,
            ],
            'not-empty 4' => [
                'notEmpty' => ['EMPTY_KEY'],
                'expectedException' => ValidationException::class,
            ],

            // test integer
            'integer 1' => [
                'integer' => [],
                'expectedValues' => $imported,
            ],
            'integer 2' => [
                'integer' => 'INTEGER_KEY', // as a string
                'expectedValues' => $imported,
            ],
            'integer 3' => [
                'integer' => ['INTEGER_KEY'],
                'expectedValues' => $imported,
            ],
            'integer 4' => [
                'boolean' => ['NEW_KEY'],
                'expectedException' => ValidationException::class,
            ],

            // test boolean
            'boolean 1' => [
                'boolean' => [],
                'expectedValues' => $imported,
            ],
            'boolean 2' => [
                'boolean' => 'BOOLEAN_KEY', // as a string
                'expectedValues' => $imported,
            ],
            'boolean 3' => [
                'boolean' => ['BOOLEAN_KEY'],
                'expectedValues' => $imported,
            ],
            'boolean 4' => [
                'boolean' => ['NEW_KEY'],
                'expectedException' => ValidationException::class,
            ],

            // test allowed-values
            'allowed-values 1' => [
                'allowedValues' => [],
                'expectedValues' => $imported,
            ],
            'allowed-values 2' => [
                'allowedValues' => ['NEW_KEY' => ['new-value1', 'new-value2']],
                'expectedValues' => $imported,
            ],
            'allowed-values 3' => [
                'allowedValues' => ['NEW_KEY' => ['new-value2', 'new-value3']],
                'expectedException' => ValidationException::class,
            ],
            'allowed-values 4' => [
                'allowedValues' => ['NEW_KEY' => []],
                'expectedException' => ValidationException::class,
            ],
            'allowed-values 5' => [
                'allowedValues' => ['EMPTY_KEY' => ['']],
                'expectedValues' => $imported,
            ],
            'allowed-values 6' => [
                'allowedValues' => [
                    'NEW_KEY' => ['new-value1', 'new-value2'],
                    'INTEGER_KEY' => ['5', '6'],
                ],
                'expectedValues' => $imported,
            ],
            'allowed-values 7' => [
                'allowedValues' => [
                    'NEW_KEY' => ['new-value1', 'new-value2'],
                    'INTEGER_KEY' => ['6', '7'],
                ],
                'expectedException' => ValidationException::class,
            ],

            // test regex
            'regex 1' => [
                'regex' => [],
                'expectedValues' => $imported,
            ],
            'regex 2' => [
                'regex' => ['NEW_KEY' => '/^new-value1$/'],
                'expectedValues' => $imported,
            ],
            'regex 3' => [
                'regex' => ['NEW_KEY' => '/^wrong-value$/'],
                'expectedException' => ValidationException::class,
            ],

            // test callback
            'callback 1' => [
                'callback' => [],
                'expectedValues' => $imported,
            ],
            'callback 2' => [
                'callback' => $validCallback,
                'expectedValues' => $imported,
            ],
            'callback 3' => [
                'callback' => $invalidCallback,
                'expectedException' => ValidationException::class,
            ],
            'callback 4' => [
                'callback' => ['NEW_KEY' => $validCallback],
                'expectedValues' => $imported,
            ],
            'callback 5' => [
                'callback' => ['NEW_KEY' => $invalidCallback],
                'expectedException' => ValidationException::class,
            ],

//            // test overriding getenv()
//            'override getenv() 1' => [
//                'updateGetenv' => false,
//            ],
//            'override getenv() 2' => [
//                'updateGetenv' => true,
//                'expectedGetenv' => $notOverridden,
//            ],
//            'override getenv() 3' => [
//                'updateGetenv' => true,
//                'override' => false,
//                'expectedGetenv' => $notOverridden,
//            ],
//            'override getenv() 4' => [
//                'updateGetenv' => true,
//                'override' => true,
//                'expectedGetenv' => $overridden,
//            ],

            // test overriding $_ENV
            'override $_ENV 1' => [
                'updateEnv' => false,
            ],
            'override $_ENV 2' => [
                'updateEnv' => true,
                'expectedEnv' => $notOverridden,
            ],
            'override $_ENV 3' => [
                'updateEnv' => true,
                'override' => false,
                'expectedEnv' => $notOverridden,
            ],
            'override $_ENV 4' => [
                'updateEnv' => true,
                'override' => true,
                'expectedEnv' => $overridden,
            ],

            // test overriding $_SERVER
            'override $_SERVER 1' => [
                'updateServer' => false,
            ],
            'override $_SERVER 2' => [
                'updateServer' => true,
                'expectedServer' => $notOverridden,
            ],
            'override $_SERVER 3' => [
                'updateServer' => true,
                'override' => false,
                'expectedServer' => $notOverridden,
            ],
            'override $_SERVER 4' => [
                'updateServer' => true,
                'override' => true,
                'expectedServer' => $overridden,
            ],
        ];

        foreach ($combinations as $index => $combination) {
            $combinations[$index] = array_merge($default, $combination);
        }
$combinations = ['filename 2' => $combinations['filename 2']];
        return $combinations;
    }

    /**
     * Test that values can be read, validated, and that $_ENV and $_SERVER are populated appropriately.
     *
     * @test
     * @dataProvider CanImportProperlyDataProvider
     * @param boolean                                  $useSafeLoad       Whether to use ->safeLoad() instead of
     *                                                                    ->load() or not.
     * @param string|null                              $envFilename       The name of the .env file to load.
     * @param string[]|null                            $pick              The keys to pick - ignores the rest.
     * @param string[]|null                            $ignore            The keys to ignore.
     * @param string[]|null                            $required          The required values.
     * @param string[]|null                            $notEmpty          The values that cannot be empty when present.
     * @param string[]|null                            $integer           The values that must be integers when
     *                                                                    present.
     * @param string[]|null                            $boolean           The values that must be booleans when
     *                                                                    present.
     * @param string[]|null                            $allowedValues     The allowed values certain keys.
     * @param string[]|null                            $regex             The regex to validate against.
     * @param callable|callable[]|string|string[]|null $callback          The callbacks to validate with.
//     * @param boolean|null                             $updateGetenv      Whether to update getenv() or not.
     * @param boolean|null                             $updateEnv         Whether to update $_ENV or not.
     * @param boolean|null                             $updateServer      Whether to update $_SERVER or not.
     * @param boolean|null                             $override          Whether to override values that already
     *                                                                    exist.
     * @param string[]                                 $expectedValues    The expected values read from the .env file.
//     * @param string[]                                 $expectedGetenv    The expected values to appear in the
     *                                                                    getenv() results.
     * @param string[]                                 $expectedEnv       The expected values to appear in the
     *                                                                    $_ENV super-global.
     * @param string[]                                 $expectedServer    The expected values to appear in the
     *                                                                    $_SERVER super-global.
     * @param string|null                              $expectedException The expected exception class.
     * @return void
     */
    public function can_import_properly(
        bool $useSafeLoad,
        $envFilename,
        $pick,
        $ignore,
        $required,
        $notEmpty,
        $integer,
        $boolean,
        $allowedValues,
        $regex,
        $callback,
//        $updateGetenv,
        $updateEnv,
        $updateServer,
        $override,
        array $expectedValues,
//        array $expectedGetenv,
        array $expectedEnv,
        array $expectedServer,
        string $expectedException = null
    ) {

        $this->customSetUp();

        $fDotEnv = self::newFluentDotEnv();

        // pre-import actions
        if (!is_null($pick)) {
            $fDotEnv->pick($pick);
        }
        if (!is_null($ignore)) {
            $fDotEnv->ignore($ignore);
        }

        // validation actions
        if (!is_null($required)) {
            $fDotEnv->required($required);
        }
        if (!is_null($notEmpty)) {
            $fDotEnv->notEmpty($notEmpty);
        }
        if (!is_null($integer)) {
            $fDotEnv->integer($integer);
        }
        if (!is_null($boolean)) {
            $fDotEnv->boolean($boolean);
        }
        if (!is_null($allowedValues)) {
            $fDotEnv->allowedValues($allowedValues);
        }
        if (!is_null($regex)) {
            $fDotEnv->regex($regex);
        }
        if (!is_null($callback)) {
            $fDotEnv->callback($callback);
        }

        // populate actions
//        if ($updateGetenv) {
//            if (!is_null($override)) {
//                $fDotEnv->populateGetEnv($override);
//            } else {
//                $fDotEnv->populateGetEnv();
//            }
//        }
        if ($updateEnv) {
            if (!is_null($override)) {
                $fDotEnv->populateEnv($override);
            } else {
                $fDotEnv->populateEnv();
            }
        }
        if ($updateServer) {
            if (!is_null($override)) {
                $fDotEnv->populateServer($override);
            } else {
                $fDotEnv->populateServer();
            }
        }


        // act + assert
        if ($expectedException) {
            $this->assertThrows(
                $expectedException,
                function () use (&$fDotEnv, $useSafeLoad, $envFilename) {

                    $useSafeLoad
                        ? $fDotEnv->safeLoad(__DIR__ . '/input/' . $envFilename)
                        : $fDotEnv->load(__DIR__ . '/input/' . $envFilename);
                }
            );
        } else {
            $values = $useSafeLoad
                ? $fDotEnv->safeLoad(__DIR__ . '/input/' . $envFilename)->all()
                : $fDotEnv->load(__DIR__ . '/input/' . $envFilename)->all();

            $this->assertSame($expectedValues, $values);
//            foreach ($expectedGetenv as $key => $value) {
//                $this->assertSame($value, getenv($key));
//            }
            $this->assertSame($expectedEnv, $_ENV);
            $this->assertSame($expectedServer, $_SERVER);
        }
    }

    /**
     * Generate data for the can_call_methods_in_different_ways test below.
     *
     * @return array[]
     */
    public static function CanCallMethodsInDifferentWaysDataProvider(): array
    {
        $imported = [
            'INITIAL_KEY' => 'override-value',
            'NEW_KEY' => 'new-value1',
            'EMPTY_KEY' => '',
            'INTEGER_KEY' => '5',
            'BOOLEAN_KEY' => 'true',
        ];
        $newKeyOnly = [
            'NEW_KEY' => 'new-value1',
        ];
        $ignoredNewKey = [
            'INITIAL_KEY' => 'override-value',
            'EMPTY_KEY' => '',
            'INTEGER_KEY' => '5',
            'BOOLEAN_KEY' => 'true',
        ];

        $default = [
            'methodsAndParams' => [],
            'expectedValues' => $imported,
            'expectedException' => null
        ];

        $build1 = function (
            string $name,
            $method,
            $validKey,
            $invalidKey,
            $expectedValues,
            $throwExeceptionWhenInvalidValuePresent
        ) {
            $expectedException = ($throwExeceptionWhenInvalidValuePresent ? ValidationException::class : null);

            return [
                // ->integer('KEY')
                $name . ' 1' => [
                    'methodsAndParams' => [
                        [
                            'method' => $method,
                            'parameters' => [$validKey],
                        ],
                    ],
                    'expectedValues' => $expectedValues,
                ],
                // ->integer('KEY')
                // ->integer('INVALID_KEY')
                $name . ' 2' => [
                    'methodsAndParams' => [
                        [
                            'method' => $method,
                            'parameters' => [$validKey],
                        ],
                        [
                            'method' => $method,
                            'parameters' => [$invalidKey],
                        ],
                    ],
                    'expectedValues' => $expectedValues,
                    'expectedException' => $expectedException,
                ],
                // ->integer('INVALID_KEY')
                // ->integer('KEY')
                $name . ' 3' => [
                    'methodsAndParams' => [
                        [
                            'method' => $method,
                            'parameters' => [$invalidKey],
                        ],
                        [
                            'method' => $method,
                            'parameters' => [$validKey],
                        ],
                    ],
                    'expectedValues' => $expectedValues,
                    'expectedException' => $expectedException,
                ],
                // ->integer(['KEY', 'INVALID_KEY'])
                $name . ' 4' => [
                    'methodsAndParams' => [
                        [
                            'method' => $method,
                            'parameters' => [[$validKey, $invalidKey]],
                        ],
                    ],
                    'expectedValues' => $expectedValues,
                    'expectedException' => $expectedException,
                ],
                // ->integer(['KEY'])
                // ->integer(['INVALID_KEY'])
                $name . ' 5' => [
                    'methodsAndParams' => [
                        [
                            'method' => $method,
                            'parameters' => [[$validKey]],
                        ],
                        [
                            'method' => $method,
                            'parameters' => [[$invalidKey]],
                        ],
                    ],
                    'expectedValues' => $expectedValues,
                    'expectedException' => $expectedException,
                ],
                // ->integer(['INVALID_KEY'])
                // ->integer(['KEY'])
                $name . ' 6' => [
                    'methodsAndParams' => [
                        [
                            'method' => $method,
                            'parameters' => [[$invalidKey]],
                        ],
                        [
                            'method' => $method,
                            'parameters' => [[$validKey]],
                        ],
                    ],
                    'expectedValues' => $expectedValues,
                    'expectedException' => $expectedException,
                ],
                // ->integer('KEY', 'INVALID_KEY')
                $name . ' 7' => [
                    'methodsAndParams' => [
                        [
                            'method' => $method,
                            'parameters' => [$validKey, $invalidKey],
                        ],
                    ],
                    'expectedValues' => $expectedValues,
                    'expectedException' => $expectedException,
                ],
                // ->integer('INVALID_KEY', 'KEY')
                $name . ' 8' => [
                    'methodsAndParams' => [
                        [
                            'method' => $method,
                            'parameters' => [$invalidKey, $validKey],
                        ],
                    ],
                    'expectedValues' => $expectedValues,
                    'expectedException' => $expectedException,
                ],
            ];
        };

        $build2 = function (
            string $name,
            $method,
            $key1,
            $key2,
            $validValue,
            $invalidValue,
            $throwExeceptionWhenInvalidValuePresent
        ) {
            $expectedException = ($throwExeceptionWhenInvalidValuePresent ? ValidationException::class : null);

            return [
                // ->regex('KEY', '/^valid$/');
                $name . ' 1' => [
                    'methodsAndParams' => [
                        [
                            'method' => $method,
                            'parameters' => [$key1, $validValue],
                        ],
                    ],
                ],
                // ->regex('KEY', '/^valid$/');
                // ->regex('KEY', '/^invalid$/');
                $name . ' 2' => [
                    'methodsAndParams' => [
                        [
                            'method' => $method,
                            'parameters' => [$key1, $validValue],
                        ],
                        [
                            'method' => $method,
                            'parameters' => [$key1, $invalidValue],
                        ],
                    ],
                    'expectedException' => $expectedException,
                ],
                // ->regex('KEY', '/^invalid$/');
                // ->regex('KEY', '/^valid$/');
                $name . ' 3' => [
                    'methodsAndParams' => [
                        [
                            'method' => $method,
                            'parameters' => [$key1, $invalidValue],
                        ],
                        [
                            'method' => $method,
                            'parameters' => [$key1, $validValue],
                        ],
                    ],
                    'expectedException' => $expectedException,
                ],
                // ->regex(['KEY1', 'KEY2'], '/^valid$/');
                $name . ' 4' => [
                    'methodsAndParams' => [
                        [
                            'method' => $method,
                            'parameters' => [[$key1, $key2], $validValue],
                        ],
                    ],
                ],
                // ->regex(['KEY1', 'KEY2'], '/^valid$/');
                // ->regex(['KEY1', 'KEY2'], '/^invalid$/');
                $name . ' 5' => [
                    'methodsAndParams' => [
                        [
                            'method' => $method,
                            'parameters' => [[$key1, $key2], $validValue],
                        ],
                        [
                            'method' => $method,
                            'parameters' => [[$key1, $key2], $invalidValue],
                        ],
                    ],
                    'expectedException' => $expectedException,
                ],
                // ->regex(['KEY1', 'KEY2'], '/^invalid$/');
                // ->regex(['KEY1', 'KEY2'], '/^valid$/');
                $name . ' 6' => [
                    'methodsAndParams' => [
                        [
                            'method' => $method,
                            'parameters' => [[$key1, $key2], $invalidValue],
                        ],
                        [
                            'method' => $method,
                            'parameters' => [[$key1, $key2], $validValue],
                        ],
                    ],
                    'expectedException' => $expectedException,
                ],
                // ->regex(['KEY' => '/^valid$/']);
                $name . ' 7' => [
                    'methodsAndParams' => [
                        [
                            'method' => $method,
                            'parameters' => [[$key1 => $validValue]],
                        ],
                    ],
                ],
                // ->regex(['KEY' => '/^valid$/']);
                // ->regex(['KEY' => '/^invalid$/']);
                $name . ' 8' => [
                    'methodsAndParams' => [
                        [
                            'method' => $method,
                            'parameters' => [[$key1 => $validValue]],
                        ],
                        [
                            'method' => $method,
                            'parameters' => [[$key1 => $invalidValue]],
                        ],
                    ],
                    'expectedException' => $expectedException,
                ],
                // ->regex(['KEY' => '/^invalid$/']);
                // ->regex(['KEY' => '/^valid$/']);
                $name . ' 9' => [
                    'methodsAndParams' => [
                        [
                            'method' => $method,
                            'parameters' => [[$key1 => $invalidValue]],
                        ],
                        [
                            'method' => $method,
                            'parameters' => [[$key1 => $validValue]],
                        ],
                    ],
                    'expectedException' => $expectedException,
                ]
            ];
        };

        $validCallback = function (string $key, $value) {
            return true;
        };
        $invalidCallback = function (string $key, $value) {
            return false;
        };

        $combinations = array_merge(
            [
                'default' => [],
            ],
            $build1('pick', 'pick', 'NEW_KEY', 'MISSING_KEY', $newKeyOnly, false),
            $build1('ignore', 'ignore', 'NEW_KEY', 'MISSING_KEY', $ignoredNewKey, false),
            $build1('required', 'required', 'NEW_KEY', 'MISSING_KEY', $imported, true),
            $build1('not-empty', 'notEmpty', 'INTEGER_KEY', 'EMPTY_KEY', $imported, true),
            $build1('integer', 'integer', 'INTEGER_KEY', 'NEW_KEY', $imported, true),
            $build1('boolean', 'boolean', 'BOOLEAN_KEY', 'NEW_KEY', $imported, true),
            $build2('allowed-values', 'allowedValues', 'NEW_KEY', 'MISSING_KEY', ['new-value1'], ['new-val2'], false),
            $build2('regex', 'regex', 'NEW_KEY', 'MISSING_KEY', '/^new-value1$/', '/^some-other-value$/', true),
            $build2('callback', 'callback', 'NEW_KEY', 'MISSING_KEY', $validCallback, $invalidCallback, true),
            [
                // ->callback($validCallback);
                'callaback 10' => [
                    'methodsAndParams' => [
                        [
                            'method' => 'callback',
                            'parameters' => [$validCallback],
                        ],
                    ],
                ],
                // ->callback($invalidCallback);
                'callaback 11' => [
                    'methodsAndParams' => [
                        [
                            'method' => 'callback',
                            'parameters' => [$invalidCallback],
                        ],
                    ],
                    'expectedException' => ValidationException::class,
                ],
                // ->callback($validCallback);
                // ->callback($invalidCallback);
                'callaback 12' => [
                    'methodsAndParams' => [
                        [
                            'method' => 'callback',
                            'parameters' => [$validCallback],
                        ],
                        [
                            'method' => 'callback',
                            'parameters' => [$invalidCallback],
                        ],
                    ],
                    'expectedException' => ValidationException::class,
                ],
                // ->callback($invalidCallback);
                // ->callback($validCallback);
                'callaback 13' => [
                    'methodsAndParams' => [
                        [
                            'method' => 'callback',
                            'parameters' => [$invalidCallback],
                        ],
                        [
                            'method' => 'callback',
                            'parameters' => [$validCallback],
                        ],
                    ],
                    'expectedException' => ValidationException::class,
                ],
            ]
        );

        foreach ($combinations as $index => $combination) {
            $combinations[$index] = array_merge($default, $combination);
        }
        return $combinations;
    }

    /**
     * Test the different ways that various methods can be called.
     *
     * @test
     * @dataProvider CanCallMethodsInDifferentWaysDataProvider
     * @param mixed[][]   $methodsAndParams  The methods to call and the parameters to pass to them.
     * @param string[]    $expectedValues    The expected values read from the .env file.
     * @param string|null $expectedException The expected exception class.
     * @return void
     */
    public function can_call_methods_in_different_ways(
        array $methodsAndParams,
        array $expectedValues,
        string $expectedException = null
    ) {
        $this->customSetUp();

        $fDotEnv = self::newFluentDotEnv();

        foreach ($methodsAndParams as $oneMethodAndParams) {
            $method = $oneMethodAndParams['method'];
            $parameters = $oneMethodAndParams['parameters'];
            call_user_func_array([$fDotEnv, $method], $parameters);
        }

         // act + assert
        if ($expectedException) {
            $this->assertThrows(
                $expectedException,
                function () use (&$fDotEnv) {
                    $fDotEnv->load(__DIR__ . '/input/.env');
                }
            );
        } else {
            $values = $fDotEnv->load(__DIR__ . '/input/.env')->all();
            $this->assertSame($expectedValues, $values);
        }
    }

    /**
     * Generate data for the can_call_validation_after_load test below.
     *
     * @return array[]
     */
    public static function canCallValidationAfterLoadDataProvider(): array
    {
        $validCallback = function (string $key, $value) {
            return true;
        };
        $invalidCallback = function (string $key, $value) {
            return false;
        };

        $allValues = [
            'INITIAL_KEY' => 'override-value',
            'NEW_KEY' => 'new-value1',
            'EMPTY_KEY' => '',
            'INTEGER_KEY' => '5',
            'BOOLEAN_KEY' => 'true',
        ];

        return [
            // pick
            'pick 1' => [
                'method' => 'pick',
                'params' => ['NEW_KEY'],
                'expectedValues' => ['NEW_KEY' => 'new-value1'],
                'expectedException' => null,
            ],
            // ignore
            'ignore 1' => [
                'method' => 'ignore',
                'params' => ['NEW_KEY'],
                'expectedValues' => [
                    'INITIAL_KEY' => 'override-value',
                    'EMPTY_KEY' => '',
                    'INTEGER_KEY' => '5',
                    'BOOLEAN_KEY' => 'true',
                ],
                'expectedException' => null,
            ],
            // required
            'required 1' => [
                'method' => 'required',
                'params' => ['NEW_KEY'],
                'expectedValues' => $allValues,
                'expectedException' => null,
            ],
            'required 2' => [
                'method' => 'required',
                'params' => ['MISSING_KEY'],
                'expectedValues' => $allValues,
                'expectedException' => ValidationException::class,
            ],
            // notEmpty
            'notEmpty 1' => [
                'method' => 'notEmpty',
                'params' => ['NEW_KEY'],
                'expectedValues' => $allValues,
                'expectedException' => null,
            ],
            'notEmpty 2' => [
                'method' => 'notEmpty',
                'params' => ['EMPTY_KEY'],
                'expectedValues' => $allValues,
                'expectedException' => ValidationException::class,
            ],
            // integer
            'integer 1' => [
                'method' => 'integer',
                'params' => ['INTEGER_KEY'],
                'expectedValues' => $allValues,
                'expectedException' => null,
            ],
            'integer 2' => [
                'method' => 'integer',
                'params' => ['EMPTY_KEY'],
                'expectedValues' => $allValues,
                'expectedException' => ValidationException::class,
            ],
            // boolean
            'boolean 1' => [
                'method' => 'boolean',
                'params' => ['BOOLEAN_KEY'],
                'expectedValues' => $allValues,
                'expectedException' => null,
            ],
            'boolean 2' => [
                'method' => 'boolean',
                'params' => ['EMPTY_KEY'],
                'expectedValues' => $allValues,
                'expectedException' => ValidationException::class,
            ],
            // allowedValues
            'allowedValues 1' => [
                'method' => 'allowedValues',
                'params' => ['NEW_KEY', ['new-value1']],
                'expectedValues' => $allValues,
                'expectedException' => null,
            ],
            'allowedValues 2' => [
                'method' => 'allowedValues',
                'params' => ['NEW_KEY', ['some-other-value']],
                'expectedValues' => $allValues,
                'expectedException' => ValidationException::class,
            ],
            // regex
            'regex 1' => [
                'method' => 'regex',
                'params' => ['NEW_KEY', '/^new-value1$/'],
                'expectedValues' => $allValues,
                'expectedException' => null,
            ],
            'regex 2' => [
                'method' => 'regex',
                'params' => ['NEW_KEY', '/^some-other-value$/'],
                'expectedValues' => $allValues,
                'expectedException' => ValidationException::class,
            ],
            // callback - global
            'callback-global 1' => [
                'method' => 'callback',
                'params' => [$validCallback],
                'expectedValues' => $allValues,
                'expectedException' => null,
            ],
            'callback-global 2' => [
                'method' => 'callback',
                'params' => [$invalidCallback],
                'expectedValues' => $allValues,
                'expectedException' => ValidationException::class,
            ],
            // callback - keyed
            'callback-keyed 1' => [
                'method' => 'callback',
                'params' => ['NEW_KEY', $validCallback],
                'expectedValues' => $allValues,
                'expectedException' => null,
            ],
            'callback-keyed 2' => [
                'method' => 'callback',
                'params' => ['NEW_KEY', $invalidCallback],
                'expectedValues' => $allValues,
                'expectedException' => ValidationException::class,
            ],
        ];
    }

    /**
     * Test that the validation methods can be called after ->load() has been run.
     *
     * @test
     * @dataProvider canCallValidationAfterLoadDataProvider
     * @param string      $method            The validation method to call.
     * @param mixed[]     $params            The parameters to pass to the method.
     * @param mixed[]     $expectedValues    The expected values.
     * @param string|null $expectedException The expected exception class.
     * @return void
     */
    public function can_call_validation_after_load(
        string $method,
        array $params,
        array $expectedValues,
        string $expectedException = null
    ) {

        $this->customSetUp();
        $fDotEnv = self::newFluentDotEnv()->load(__DIR__ . '/input/.env');
        $callable = [$fDotEnv, $method];
        if (!is_callable($callable)) {
            self::fail();
        }

        if ($expectedException) {
            $this->assertThrows(
                $expectedException,
                function () use ($callable, $params) {
                    call_user_func_array($callable, $params);
                }
            );
        } else {
            call_user_func_array($callable, $params);
            $this->assertTrue(true);
        }

        $this->assertSame($expectedValues, $fDotEnv->all());
    }

    /**
     * Test that the pick method works after values have been loaded.
     *
     * @test
     * @return void
     */
    public function pick_after_load_works()
    {
        $this->customSetUp();

        $fDotEnv = self::newFluentDotEnv()->load(__DIR__ . '/input/.env')->pick('NEW_KEY')->pick('EMPTY_KEY');
        $this->assertSame(
            [
                'NEW_KEY' => 'new-value1',
                'EMPTY_KEY' => '',
            ],
            $fDotEnv->all()
        );

        $fDotEnv = self::newFluentDotEnv()->load(__DIR__ . '/input/.env');
        $this->assertSame(
            [
                'INITIAL_KEY' => 'override-value',
                'NEW_KEY' => 'new-value1',
                'EMPTY_KEY' => '',
                'INTEGER_KEY' => '5',
                'BOOLEAN_KEY' => 'true',
            ],
            $fDotEnv->all()
        );
    }

    /**
     * Test that loading multiple .env files works
     *
     * @test
     * @return void
     */
    public function loading_multiple_env_files_works()
    {
        $this->customSetUp();

        // passed in an array
        $fDotEnv = self::newFluentDotEnv()
            ->load([__DIR__ . '/input/1.env']);
        $this->assertSame(['MY_VALUE1' => 'a'], $fDotEnv->all());

        $fDotEnv = self::newFluentDotEnv()
            ->load([__DIR__ . '/input/1.env', __DIR__ . '/input/2.env']);
        $this->assertSame(['MY_VALUE1' => 'b'], $fDotEnv->all());

        $fDotEnv = self::newFluentDotEnv()
            ->load([__DIR__ . '/input/2.env', __DIR__ . '/input/1.env']);
        $this->assertSame(['MY_VALUE1' => 'a'], $fDotEnv->all());

        // passed as separate parameters
        $fDotEnv = self::newFluentDotEnv()
            ->load(__DIR__ . '/input/1.env');
        $this->assertSame(['MY_VALUE1' => 'a'], $fDotEnv->all());

        $fDotEnv = self::newFluentDotEnv()
            ->load(__DIR__ . '/input/1.env', __DIR__ . '/input/2.env');
        $this->assertSame(['MY_VALUE1' => 'b'], $fDotEnv->all());

        $fDotEnv = self::newFluentDotEnv()
            ->load(__DIR__ . '/input/2.env', __DIR__ . '/input/1.env');
        $this->assertSame(['MY_VALUE1' => 'a'], $fDotEnv->all());

        // ->load called multiple times
        $this->assertThrows(
            AlreadyLoadedException::class,
            function () {
                self::newFluentDotEnv()
                    ->load(__DIR__ . '/input/1.env')
                    ->load(__DIR__ . '/input/2.env');
            }
        );

        $this->assertThrows(
            AlreadyLoadedException::class,
            function () {
                self::newFluentDotEnv()
                    ->safeLoad(__DIR__ . '/input/1.env')
                    ->safeLoad(__DIR__ . '/input/2.env');
            }
        );

        // missing file
        $this->assertThrows(
            InvalidPathException::class,
            function () {
                self::newFluentDotEnv()
                    ->load([__DIR__ . '/input/missing.env']);
            }
        );
        $this->assertThrows(
            InvalidPathException::class,
            function () {
                self::newFluentDotEnv()
                    ->load([__DIR__ . '/input/1.env', __DIR__ . '/input/missing.env', __DIR__ . '/input/2.env']);
            }
        );

        // missing file - but with safeLoad()
        $fDotEnv = self::newFluentDotEnv()
            ->safeLoad(__DIR__ . '/input/missing.env');
        $this->assertSame([], $fDotEnv->all());

        $fDotEnv = self::newFluentDotEnv()
            ->safeLoad(__DIR__ . '/input/1.env', __DIR__ . '/input/missing.env', __DIR__ . '/input/2.env');
        $this->assertSame(['MY_VALUE1' => 'b'], $fDotEnv->all());
    }

    /**
     * Generate data for the can_cast_properly test below.
     *
     * @return array[]
     */
    public static function canCastProperlyDataProvider(): array
    {
        // symfony/dotenv ^3.3 on Windows does not support uppercase values
        // perform an initial test to work out if such values should be excluded from the test
        $fDotEnv = self::newFluentDotEnv()->safeLoad(__DIR__ . '/input/cast.env');
        $supportsUppercaseValues = $fDotEnv->get('TrUe') === 'TrUe';

        $keysWithUppercaseValues = [
            'TrUe',
            'TRUE',
            'FaLsE',
            'FALSE',
            'On',
            'ON',
            'Off',
            'OFF',
            'YeS',
            'YES',
            'No',
            'NO',
        ];
        $hasUpperCaseChar = function ($value) use ($keysWithUppercaseValues): bool {
            return in_array($value, $keysWithUppercaseValues, true);
        };

        $includeKey = function ($value) use ($supportsUppercaseValues, $hasUpperCaseChar): bool {
            return $supportsUppercaseValues || !$hasUpperCaseChar($value);
        };



        $boolean = [
            'true' => true,
            'TrUe' => true,
            'TRUE' => true,
            'false' => false,
            'FaLsE' => false,
            'FALSE' => false,
            'on' => true,
            'On' => true,
            'ON' => true,
            'off' => false,
            'Off' => false,
            'OFF' => false,
            'yes' => true,
            'YeS' => true,
            'YES' => true,
            'no' => false,
            'No' => false,
            'NO' => false,
            'one' => true,
            'zero' => false,
            'blah' => null,
        ];
        $integer = [
            'negativeBigNumber' => -12345678,
            'negativeOne' => -1,
            'zero' => 0,
            'one' => 1,
            'bigNumber' => 12345678,
            'blah' => null,
        ];

        $return = [];
        foreach (['castBoolean' => $boolean, 'castInteger' => $integer] as $castMethod => $tests) {
            foreach ($tests as $key => $expected) {

                if (!$includeKey($key)) {
                    continue;
                }

                $return[] = [
                    'castMethod' => $castMethod,
                    'key' => (string) $key,
                    'expected' => $expected,
                ];
            }
        }
        return $return;
    }

    /**
     * Test that values are cast properly.
     *
     * @test
     * @dataProvider canCastProperlyDataProvider
     *
     * @param string $castMethod The cast method to call.
     * @param string $key        The key to retrieve.
     * @param mixed  $expected   The expected result from the cast call.
     * @return void
     */
    public function can_cast_properly(string $castMethod, string $key, $expected)
    {
        $fDotEnv = self::newFluentDotEnv()->safeLoad(__DIR__ . '/input/cast.env');
        $this->assertSame($expected, $fDotEnv->$castMethod($key));
    }

    /**
     * Generate data for the can_get_multiple_values test below.
     *
     * @return array[]
     */
    public static function canGetMultipleValuesDataProvider(): array
    {
        return [
            // GET
            'get() - single param' => [
                'method' => 'get',
                'params' => ['ONE'],
                'expected' => '1',
            ],
            'get() - single param as an array' => [
                'method' => 'get',
                'params' => [['ONE']],
                'expected' => ['ONE' => '1'],
            ],
            'get() - two params as an array' => [
                'method' => 'get',
                'params' => [['ONE', 'TWO']],
                'expected' => ['ONE' => '1', 'TWO' => '2'],
            ],
            'get() - three params as an array' => [
                'method' => 'get',
                'params' => [['ONE', 'TWO', 'THREE']],
                'expected' => ['ONE' => '1', 'TWO' => '2', 'THREE' => '3'],
            ],
            'get() - two params as separate params' => [
                'method' => 'get',
                'params' => ['ONE', 'TWO'],
                'expected' => ['ONE' => '1', 'TWO' => '2'],
            ],
            'get() - three params as separate params' => [
                'method' => 'get',
                'params' => ['ONE', 'TWO', 'THREE'],
                'expected' => ['ONE' => '1', 'TWO' => '2', 'THREE' => '3'],
            ],
            'get() - mixed params and array 1' => [
                'method' => 'get',
                'params' => ['ONE', ['TWO']],
                'expected' => ['ONE' => '1', 'TWO' => '2'],
            ],
            'get() - mixed params and array 2' => [
                'method' => 'get',
                'params' => ['ONE', ['TWO', 'THREE']],
                'expected' => ['ONE' => '1', 'TWO' => '2', 'THREE' => '3'],
            ],
            'get() - mixed params and array 3' => [
                'method' => 'get',
                'params' => [['ONE'], ['TWO', 'THREE']],
                'expected' => ['ONE' => '1', 'TWO' => '2', 'THREE' => '3'],
            ],
            'get() - mixed params and array 4' => [
                'method' => 'get',
                'params' => [['ONE', 'TWO'], 'THREE'],
                'expected' => ['ONE' => '1', 'TWO' => '2', 'THREE' => '3'],
            ],

            // INTEGER
            'castInteger() - single param' => [
                'method' => 'castInteger',
                'params' => ['ONE'],
                'expected' => 1,
            ],
            'castInteger() - single param as an array' => [
                'method' => 'castInteger',
                'params' => [['ONE']],
                'expected' => ['ONE' => 1],
            ],
            'castInteger() - two params as an array' => [
                'method' => 'castInteger',
                'params' => [['ONE', 'TWO']],
                'expected' => ['ONE' => 1, 'TWO' => 2],
            ],
            'castInteger() - three params as an array' => [
                'method' => 'castInteger',
                'params' => [['ONE', 'TWO', 'THREE']],
                'expected' => ['ONE' => 1, 'TWO' => 2, 'THREE' => 3],
            ],
            'castInteger() - two params as separate params' => [
                'method' => 'castInteger',
                'params' => ['ONE', 'TWO'],
                'expected' => ['ONE' => 1, 'TWO' => 2],
            ],
            'castInteger() - three params as separate params' => [
                'method' => 'castInteger',
                'params' => ['ONE', 'TWO', 'THREE'],
                'expected' => ['ONE' => 1, 'TWO' => 2, 'THREE' => 3],
            ],
            'castInteger() - mixed params and array 1' => [
                'method' => 'castInteger',
                'params' => ['ONE', ['TWO']],
                'expected' => ['ONE' => 1, 'TWO' => 2],
            ],
            'castInteger() - mixed params and array 2' => [
                'method' => 'castInteger',
                'params' => ['ONE', ['TWO', 'THREE']],
                'expected' => ['ONE' => 1, 'TWO' => 2, 'THREE' => 3],
            ],
            'castInteger() - mixed params and array 3' => [
                'method' => 'castInteger',
                'params' => [['ONE'], ['TWO', 'THREE']],
                'expected' => ['ONE' => 1, 'TWO' => 2, 'THREE' => 3],
            ],
            'castInteger() - mixed params and array 4' => [
                'method' => 'castInteger',
                'params' => [['ONE', 'TWO'], 'THREE'],
                'expected' => ['ONE' => 1, 'TWO' => 2, 'THREE' => 3],
            ],

            // BOOLEAN
            'castBoolean() - single param' => [
                'method' => 'castBoolean',
                'params' => ['TRUE'],
                'expected' => true,
            ],
            'castBoolean() - single param as an array' => [
                'method' => 'castBoolean',
                'params' => [['TRUE']],
                'expected' => ['TRUE' => true],
            ],
            'castBoolean() - two params as an array' => [
                'method' => 'castBoolean',
                'params' => [['TRUE', 'FALSE']],
                'expected' => ['TRUE' => true, 'FALSE' => false],
            ],
            'castBoolean() - three params as an array' => [
                'method' => 'castBoolean',
                'params' => [['TRUE', 'FALSE', 'YES']],
                'expected' => ['TRUE' => true, 'FALSE' => false, 'YES' => true],
            ],
            'castBoolean() - two params as separate params' => [
                'method' => 'castBoolean',
                'params' => ['TRUE', 'FALSE'],
                'expected' => ['TRUE' => true, 'FALSE' => false],
            ],
            'castBoolean() - three params as separate params' => [
                'method' => 'castBoolean',
                'params' => ['TRUE', 'FALSE', 'YES'],
                'expected' => ['TRUE' => true, 'FALSE' => false, 'YES' => true],
            ],
            'castBoolean() - mixed params and array 1' => [
                'method' => 'castBoolean',
                'params' => ['TRUE', ['FALSE']],
                'expected' => ['TRUE' => true, 'FALSE' => false],
            ],
            'castBoolean() - mixed params and array 2' => [
                'method' => 'castBoolean',
                'params' => ['TRUE', ['FALSE', 'YES']],
                'expected' => ['TRUE' => true, 'FALSE' => false, 'YES' => true],
            ],
            'castBoolean() - mixed params and array 3' => [
                'method' => 'castBoolean',
                'params' => [['TRUE'], ['FALSE', 'YES']],
                'expected' => ['TRUE' => true, 'FALSE' => false, 'YES' => true],
            ],
            'castBoolean() - mixed params and array 4' => [
                'method' => 'castBoolean',
                'params' => [['TRUE', 'FALSE'], 'YES'],
                'expected' => ['TRUE' => true, 'FALSE' => false, 'YES' => true],
            ],
        ];
    }

    /**
     * Test that values are retrieved properly when getting several at once.
     *
     * @test
     * @dataProvider canGetMultipleValuesDataProvider
     *
     * @param string              $method   The method to fetch values with.
     * @param string[]|string[][] $params   The parameters to pass.
     * @param mixed[]|boolean     $expected The expected result from the cast call.
     * @return void
     */
    public function can_get_multiple_values(string $method, array $params, $expected)
    {
        $fDotEnv = self::newFluentDotEnv()->safeLoad(__DIR__ . '/input/many_values.env');

        $callable = [$fDotEnv, $method];
        if (is_callable($callable)) {
            $output = call_user_func_array($callable, $params);
            $this->assertSame($expected, $output);
        }
    }

    /**
     * Test that the ->load() doesn't change the existing getenv(), $_SERVER and $_ENV values.
     *
     * @test
     * @return void
     */
    public function current_env_values_arent_changed_by_loading_process()
    {
        $this->customSetUp();

        $fDotEnv = self::newFluentDotEnv()->load(__DIR__ . '/input/.env');

        // the loaded .env values
        $this->assertSame('', $fDotEnv->get('UNTOUCHED_KEY'));
        $this->assertSame('override-value', $fDotEnv->get('INITIAL_KEY'));
        $this->assertSame('new-value1', $fDotEnv->get('NEW_KEY'));

        // the getenv() values
        $this->assertSame('untouched-value', getenv('UNTOUCHED_KEY'));
        $this->assertSame('initial-value', getenv('INITIAL_KEY'));
        $this->assertFalse(getenv('NEW_KEY'));

        // the $_SERVER values
        $this->assertSame('untouched-value', $_SERVER['UNTOUCHED_KEY']);
        $this->assertSame('initial-value', $_SERVER['INITIAL_KEY']);
        $this->assertFalse(array_key_exists('NEW_KEY', $_SERVER));

        // the $_ENV values
        $this->assertSame('untouched-value', $_ENV['UNTOUCHED_KEY']);
        $this->assertSame('initial-value', $_ENV['INITIAL_KEY']);
        $this->assertFalse(array_key_exists('NEW_KEY', $_ENV));
    }
}
