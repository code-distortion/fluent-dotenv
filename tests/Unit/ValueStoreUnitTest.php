<?php

namespace CodeDistortion\FluentDotEnv\Tests\Unit;

use CodeDistortion\FluentDotEnv\Misc\ValueStore;
use CodeDistortion\FluentDotEnv\Tests\PHPUnitTestCase;

/**
 * Test the ValueStore class
 *
 * @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
class ValueStoreUnitTest extends PHPUnitTestCase
{
    /**
     * Test that an array of initial values can be passed to ValueStore.
     *
     * @test
     * @return void
     */
    public function test_that_valuestore_accepts_values_upon_instantiation()
    {
        $valueStore = new ValueStore(); // no parameters
        self::assertSame([], $valueStore->all());

        $valueStore = new ValueStore([]); // empty array
        self::assertSame([], $valueStore->all());

        $valueStore = new ValueStore(['abc' => 'ABC', 'def' => 'DEF']); // array with values
        self::assertSame(['abc' => 'ABC', 'def' => 'DEF'], $valueStore->all());
    }

    /**
     * Test that ValueStore lets you retrieve values.
     *
     * @return void
     */
    public function test_that_values_can_be_retrieved()
    {
        $valueStore = new ValueStore(['abc' => 'ABC', 'def' => 'DEF']);

        self::assertSame( 'ABC', $valueStore->get('abc'));
        self::assertSame( 'DEF', $valueStore->get('def'));
        self::assertNull( $valueStore->get('xyz'));
        self::assertNull( $valueStore->get(''));
    }

    /**
     * Test that ValueStore lets you check if keys are set.
     *
     * @return void
     */
    public function test_that_values_existence_can_be_checked()
    {
        $valueStore = new ValueStore(['abc' => 'ABC', 'def' => 'DEF']);

        self::assertTrue( $valueStore->hasKey('abc'));
        self::assertTrue( $valueStore->hasKey('def'));
        self::assertFalse( $valueStore->hasKey('xyz'));
        self::assertFalse( $valueStore->hasKey(''));

        // restrict the set by picking
        $valueStore->pick(['abc']);
        self::assertTrue( $valueStore->hasKey('abc'));
        self::assertFalse( $valueStore->hasKey('def')); // not available after "picking"
        self::assertFalse( $valueStore->hasKey('xyz'));
        self::assertFalse( $valueStore->hasKey(''));
    }

    /**
     * Test that ValueStore lets you pick particular values to use.
     *
     * @return void
     */
    public function test_that_values_are_picked()
    {
        $valueStore = new ValueStore(['abc' => 'ABC', 'def' => 'DEF']);

        $valueStore->pick(['abc']);
        self::assertSame(['abc' => 'ABC'], $valueStore->all());

        $valueStore->pick(['xyz']);
        self::assertSame(['abc' => 'ABC'], $valueStore->all());

        $valueStore->pick([]);
        self::assertSame(['abc' => 'ABC'], $valueStore->all());

        $valueStore->pick(['def']);
        self::assertSame(['abc' => 'ABC', 'def' => 'DEF'], $valueStore->all());
    }

    /**
     * Test that ValueStore lets you forget particular keys.
     *
     * @return void
     */
    public function test_that_values_are_forgotten()
    {
        $valueStore = new ValueStore(['abc' => 'ABC', 'def' => 'DEF']);

        self::assertSame(['abc' => 'ABC', 'def' => 'DEF'], $valueStore->all());

        $valueStore->forgetKey('def');
        self::assertSame(['abc' => 'ABC'], $valueStore->all());

        $valueStore->forgetKey('xyz');
        self::assertSame(['abc' => 'ABC'], $valueStore->all());

        $valueStore->forgetKey('');
        self::assertSame(['abc' => 'ABC'], $valueStore->all());

        $valueStore->forgetKey('abc');
        self::assertSame([], $valueStore->all());
    }

    /**
     * Test that ValueStore objects can be merged.
     *
     * @test
     * @return void
     */
    public function test_that_valuestores_can_be_merged()
    {
        // no values
        $valueStore1 = new ValueStore();
        $valueStore1->merge(new ValueStore());
        self::assertSame([], $valueStore1->all());

        // values from $valueStore1
        $valueStore1 = new ValueStore(['abc' => 'ABC', 'def' => 'DEF']);
        $valueStore1->merge(new ValueStore());
        self::assertSame(['abc' => 'ABC', 'def' => 'DEF'], $valueStore1->all());

        // values from $valueStore2
        $valueStore1 = new ValueStore();
        $valueStore1->merge(new ValueStore(['abc' => 'ABC', 'def' => 'DEF']));
        self::assertSame(['abc' => 'ABC', 'def' => 'DEF'], $valueStore1->all());

        // values from both $valueStore1 and $valueStore2, with $valueStore2 overwriting 'def' from $valueStore1
        $valueStore1 = new ValueStore(['def' => 'DEF']);
        $valueStore1->merge(new ValueStore(['abc' => 'ABC', 'def' => 'DEF2']));
        self::assertSame(['def' => 'DEF2', 'abc' => 'ABC'], $valueStore1->all());

        // check that picked values are re-assessed when merging
        $valueStore1 = new ValueStore(['abc' => 'ABC']);
        $valueStore1->pick(['def']);
        $valueStore1->merge(new ValueStore(['def' => 'DEF']));
        self::assertSame(['def' => 'DEF'], $valueStore1->all());
    }
}
