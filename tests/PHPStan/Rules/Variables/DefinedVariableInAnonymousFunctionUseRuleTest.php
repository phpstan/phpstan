<?php declare(strict_types = 1);

namespace PHPStan\Rules\Variables;

class DefinedVariableInAnonymousFunctionUseRuleTest extends \PHPStan\Rules\AbstractRuleTest
{
    protected function getRule(): \PHPStan\Rules\Rule
    {
        return new DefinedVariableInAnonymousFunctionUseRule();
    }

    public function testDefinedVariables()
    {
        $this->analyse([__DIR__ . '/data/defined-variables-anonymous-function-use.php'], [
            [
                'Undefined variable: $bar',
                5,
            ],
            [
                'Undefined variable: $wrongErrorHandler',
                11,
            ],
        ]);
    }
}
