<?php declare(strict_types = 1);

namespace PHPStan\Rules\Variables;

class VariableCloningRuleTest extends \PHPStan\Rules\AbstractRuleTest
{
    protected function getRule(): \PHPStan\Rules\Rule
    {
        return new VariableCloningRule();
    }

    public function testDefinedVariables()
    {
        require_once __DIR__ . '/data/variable-cloning.php';
        $this->analyse([__DIR__ . '/data/variable-cloning.php'], [
            [
                'Cannot clone non-object variable $stringData of type string.',
                16,
            ],
            [
                'Cannot clone string.',
                17,
            ],
        ]);
    }
}
