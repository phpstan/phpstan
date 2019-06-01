<?php declare(strict_types=1);

namespace IgnoreErrorComments;

class Stub
{

    /**
     * Ignore rule inside whole method
     *
     * @phpstan-ignore PHPStan.Rules.AlwaysFailRule
     */
    public function wholeMethodIgnored(): int
    {
        echo $baz;

        return 'wrong type';
    }

    /**
     * Ignore some rules with inline comments
     */
    public function someNodesIgnored()
    {

        // @phpstan-ignore PHPStan.Rules.AlwaysFailRule
        doFoo();
        doBar();

        echo 'This is valid.';

        /** @phpstan-ignore PHPStan.Rules.AlwaysFailRule */
        echo $ignoreWithDocBlock;

    }

}

/**
 * Ignore rules inside whole class
 *
 * @phpstan-ignore PHPStan.Rules.AlwaysFailRule
 */
class IgnoredClassStub {
    public function run(): int
    {
        echo $baz;

        return 'wrong type';
    }
}
