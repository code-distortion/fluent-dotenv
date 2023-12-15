<?php

namespace CodeDistortion\FluentDotEnv\Tests\Unit;

use CodeDistortion\FluentDotEnv\Misc\GetenvSupport;
use CodeDistortion\FluentDotEnv\Tests\PHPUnitTestCase;

/**
 * Test the GetenvSupport class
 *
 * @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
class GetenvSupportUnitTest extends PHPUnitTestCase
{
    /**
     * Test GetenvSupport::getAllGetenvVariables().
     *
     * @test
     * @return void
     */
    public function test_retrieval_of_all_getenv_values()
    {
        putenv("one=ONE");
        putenv("two=TWO");
        $_ENV['one'] = 'ONE'; // for PHP 7.0 to use
        $_ENV['two'] = 'TWO';

        $vars = GetenvSupport::getAllGetenvVariables();

        self::assertArrayHasKey('one', $vars);
        self::assertSame('ONE', $vars['one']);
        self::assertArrayHasKey('two', $vars);
        self::assertSame('TWO', $vars['two']);

        self::assertSame(['one' => 'ONE'], GetenvSupport::getParticularGetenvVariables(['one', 'three']));
    }

    /**
     * Test GetenvSupport::getParticularGetenvVariables().
     *
     * @test
     * @return void
     */
    public function test_retrieval_of_particular_getenv_values()
    {
        putenv("one=ONE");
        putenv("two=TWO");
        putenv("three=THREE");
        $_ENV['one'] = 'ONE'; // for PHP 7.0 to use
        $_ENV['two'] = 'TWO';
        $_ENV['three'] = 'THREE';

        self::assertSame(
            ['two' => 'TWO', 'one' => 'ONE'],
            GetenvSupport::getParticularGetenvVariables(['two', 'one', 'four'])
        );
    }

    /**
     * Test GetenvSupport::removeGetenvVariables().
     *
     * @test
     * @return void
     */
    public function test_removal_of_particular_getenv_values()
    {
        putenv("one=ONE");
        putenv("two=TWO");
        putenv("three=THREE");

        self::assertSame('ONE', getenv('one'));
        self::assertSame('TWO', getenv('two'));
        self::assertSame('THREE', getenv('three'));

        GetenvSupport::removeGetenvVariables(['one', 'three']);

        self::assertFalse(getenv('one'));
        self::assertSame('TWO', getenv('two'));
        self::assertFalse(getenv('three'));
    }

    /**
     * Test GetenvSupport::addGetenvVariables().
     *
     * @test
     * @return void
     */
    public function test_addition_of_new_getenv_values()
    {
        putenv("one=ONE");

        self::assertSame('ONE', getenv('one'));

        GetenvSupport::addGetenvVariables(['one' => 'ONE (DIFFERENT)', 'two' => 'TWO']);

        self::assertSame('ONE (DIFFERENT)', getenv('one'));
        self::assertSame('TWO', getenv('two'));
    }

    /**
     * Test GetenvSupport::replaceAllGetenvVariables().
     *
     * @test
     * @return void
     */
    public function test_the_replacement_of_all_getenv_values()
    {
        putenv("one=ONE (SAME)");
        putenv("two=TWO");
        putenv("three=THREE");
        $_ENV['one'] = 'ONE (SAME)'; // for PHP 7.0 to use
        $_ENV['two'] = 'TWO';
        $_ENV['three'] = 'THREE';

        self::assertSame('ONE (SAME)', getenv('one'));
        self::assertSame('TWO', getenv('two'));
        self::assertSame('THREE', getenv('three'));
        self::assertFalse(getenv('four'));

        GetenvSupport::replaceAllGetenvVariables(
            ['one' => 'ONE (SAME)', 'two' => 'TWO (DIFFERENT)', 'four' => 'FOUR']
        );

        self::assertSame('ONE (SAME)', getenv('one'));
        self::assertSame('TWO (DIFFERENT)', getenv('two'));
        self::assertFalse(getenv('three'));
        self::assertSame('FOUR', getenv('four'));
    }
}
