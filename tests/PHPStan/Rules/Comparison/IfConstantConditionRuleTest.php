<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\Type;

class IfConstantConditionRuleTest extends \PHPStan\Testing\RuleTestCase
{

    protected function getRule(): \PHPStan\Rules\Rule
    {
        return new IfConstantConditionRule(
            new ConstantConditionRuleHelper(
                new ImpossibleCheckTypeHelper(
                    $this->getTypeSpecifier()
                )
            )
        );
    }

    /**
     * @return DynamicFunctionReturnTypeExtension[]
     */
    public function getDynamicFunctionReturnTypeExtensions(): array
    {
        return [
            new class implements DynamicFunctionReturnTypeExtension {

                public function isFunctionSupported(FunctionReflection $functionReflection): bool
                {
                    return $functionReflection->getName() === 'always_true';
                }

                public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
                {
                    return new ConstantBooleanType(true);
                }

            },
        ];
    }

    public function testRule(): void
    {
        require_once __DIR__ . '/data/function-definition.php';
        $this->analyse([__DIR__ . '/data/if-condition.php'], [
            [
                'If condition is always true.',
                40,
            ],
            [
                'If condition is always false.',
                45,
            ],
            [
                'If condition is always true.',
                96,
            ],
        ]);
    }
}
