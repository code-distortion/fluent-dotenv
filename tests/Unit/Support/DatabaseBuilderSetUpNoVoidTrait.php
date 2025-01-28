<?php

namespace CodeDistortion\FluentDotEnv\Tests\Unit\Support;

trait DatabaseBuilderSetUpNoVoidTrait
{
    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        // some versions of PHPUnit require these $_SERVER values to be set
        // they are only set when processing a request, so we set them here

        // PHP Fatal error:  Uncaught SebastianBergmann\Timer\RuntimeException: Cannot determine time at which the
        // request started in /home/runner/work/xxx/xxx/vendor/phpunit/php-timer/src/Timer.php:83
        if (!isset($_SERVER['REQUEST_TIME_FLOAT'])) {
            $_SERVER['REQUEST_TIME_FLOAT'] = microtime(true);
        }
        if (!isset($_SERVER['REQUEST_TIME'])) {
            $_SERVER['REQUEST_TIME'] = (int) $_SERVER['REQUEST_TIME_FLOAT'];
        }
    }
}
