<?php

namespace CodeDistortion\FluentDotEnv\Tests\Unit\Support;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * Detect the relevant TestCase setUp() method signature to use, and create an alias for the relevant trait.
 *
 * TestCase's setUp() method has different signatures in different versions of PHPUnit. This code checks which
 * version is currently being used and uses the appropriate setUp() method to match.
 */
class AssignClassAlias
{
    /**
     * Choose the correct setUp() signature to use.
     *
     * @param string $namespace The namespace to import DatabaseBuilderSetUpTrait into.
     * @return void
     */
    public static function databaseBuilderSetUpTrait(string $namespace)
    {
        // only perform this once per namespace
        $destTrait = $namespace . '\\DatabaseBuilderSetUpTrait';
        if (trait_exists($destTrait)) {
            return;
        }

        $sourceTrait = self::setUpReturnsVoid()
            ? DatabaseBuilderSetUpVoidTrait::class
            : DatabaseBuilderSetUpNoVoidTrait::class;

        class_alias($sourceTrait, $destTrait);
    }

    /**
     * Check to see if the TestCase::setUp() method returns void.
     *
     * @return boolean
     */
    private static function setUpReturnsVoid(): bool
    {
        $setupMethod = new ReflectionMethod(TestCase::class, 'setUp');
        $returnType = $setupMethod->getReturnType();
        return (($returnType) && ($returnType->getName() == 'void'));
    }
}
