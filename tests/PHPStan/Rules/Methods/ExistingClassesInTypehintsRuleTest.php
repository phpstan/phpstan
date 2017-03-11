<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PHPStan\Rules\FunctionDefinitionCheck;

class ExistingClassesInTypehintsRuleTest extends \PHPStan\Rules\AbstractRuleTest
{
    protected function getRule(): \PHPStan\Rules\Rule
    {
        return new ExistingClassesInTypehintsRule(new FunctionDefinitionCheck($this->createBroker()));
    }

    public function testExistingClassInTypehint()
    {
        $this->analyse([__DIR__ . '/data/typehints.php'], [
            [
                'Return typehint of method TestMethodTypehints\FooMethodTypehints::foo() has invalid type TestMethodTypehints\NonexistentClass.',
                7,
            ],
            [
                'Parameter $bar of method TestMethodTypehints\FooMethodTypehints::bar() has invalid typehint type TestMethodTypehints\BarMethodTypehints.',
                11,
            ],
            [
                'Parameter $bars of method TestMethodTypehints\FooMethodTypehints::lorem() has invalid typehint type TestMethodTypehints\BarMethodTypehints.',
                24,
            ],
            [
                'Return typehint of method TestMethodTypehints\FooMethodTypehints::lorem() has invalid type TestMethodTypehints\BazMethodTypehints.',
                24,
            ],
            [
                'Parameter $bars of method TestMethodTypehints\FooMethodTypehints::ipsum() has invalid typehint type TestMethodTypehints\BarMethodTypehints.',
                33,
            ],
            [
                'Return typehint of method TestMethodTypehints\FooMethodTypehints::ipsum() has invalid type TestMethodTypehints\BazMethodTypehints.',
                33,
            ],
            [
                'Parameter $bars of method TestMethodTypehints\FooMethodTypehints::dolor() has invalid typehint type TestMethodTypehints\BarMethodTypehints.',
                42,
            ],
            [
                'Return typehint of method TestMethodTypehints\FooMethodTypehints::dolor() has invalid type TestMethodTypehints\BazMethodTypehints.',
                42,
            ],
            [
                'Parameter $parent of method TestMethodTypehints\FooMethodTypehints::parentWithoutParent() has invalid typehint type parent.',
                46,
            ],
            [
                'Return typehint of method TestMethodTypehints\FooMethodTypehints::parentWithoutParent() has invalid type parent.',
                46,
            ],
            [
                'Parameter $parent of method TestMethodTypehints\FooMethodTypehints::phpDocParentWithoutParent() has invalid typehint type parent.',
                54,
            ],
            [
                'Return typehint of method TestMethodTypehints\FooMethodTypehints::phpDocParentWithoutParent() has invalid type parent.',
                54,
            ],
        ]);
    }
}
