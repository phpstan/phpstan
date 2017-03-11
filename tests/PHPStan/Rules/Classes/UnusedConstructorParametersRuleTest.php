<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PHPStan\Rules\UnusedFunctionParametersCheck;

class UnusedConstructorParametersRuleTest extends \PHPStan\Rules\AbstractRuleTest
{
    protected function getRule(): \PHPStan\Rules\Rule
    {
        return new UnusedConstructorParametersRule(new UnusedFunctionParametersCheck());
    }

    public function testUnusedConstructorParameters()
    {
        $this->analyse([__DIR__ . '/data/unused-constructor-parameters.php'], [
            [
                'Constructor of class UnusedConstructorParameters\Foo has an unused parameter $unusedParameter.',
                10,
            ],
            [
                'Constructor of class UnusedConstructorParameters\Foo has an unused parameter $anotherUnusedParameter.',
                10,
            ],
        ]);
    }
}
