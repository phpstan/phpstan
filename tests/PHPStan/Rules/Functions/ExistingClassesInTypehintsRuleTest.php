<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PHPStan\Rules\FunctionDefinitionCheck;

class ExistingClassesInTypehintsRuleTest extends \PHPStan\Rules\AbstractRuleTest
{
    protected function getRule(): \PHPStan\Rules\Rule
    {
        return new ExistingClassesInTypehintsRule(new FunctionDefinitionCheck($this->createBroker()));
    }

    public function testExistingClassInTypehint()
    {
        require_once __DIR__ . '/data/typehints.php';
        $this->analyse([__DIR__ . '/data/typehints.php'], [
            [
                'Return typehint of function TestFunctionTypehints\foo() has invalid type TestFunctionTypehints\NonexistentClass.',
                10,
            ],
            [
                'Parameter $bar of function TestFunctionTypehints\bar() has invalid typehint type TestFunctionTypehints\BarFunctionTypehints.',
                15,
            ],
            [
                'Return typehint of function TestFunctionTypehints\returnParent() has invalid type parent.',
                28,
            ],
        ]);
    }
}
