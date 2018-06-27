<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PhpParser\Node\Expr;
use PHPStan\Type\Type;

interface ScopeFactory
{

    /**
     * @param \PHPStan\Analyser\ScopeContext $context
     * @param bool $declareStrictTypes
     * @param \PHPStan\Reflection\FunctionReflection|\PHPStan\Reflection\MethodReflection|null $function
     * @param string|null $namespace
     * @param \PHPStan\Analyser\VariableTypeHolder[] $variablesTypes
     * @param \PHPStan\Analyser\VariableTypeHolder[] $moreSpecificTypes
     * @param string|null $inClosureBindScopeClass
     * @param \PHPStan\Type\Type|null $inAnonymousFunctionReturnType
     * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|null $inFunctionCall
     * @param bool $negated
     * @param bool $inFirstLevelStatement
     * @param string[] $currentlyAssignedExpressions
     *
     * @return Scope
     */
    public function create(
        ScopeContext $context,
        bool $declareStrictTypes = false,
        $function = null,
        ?string $namespace = null,
        array $variablesTypes = [],
        array $moreSpecificTypes = [],
        ?string $inClosureBindScopeClass = null,
        ?Type $inAnonymousFunctionReturnType = null,
        ?Expr $inFunctionCall = null,
        bool $negated = false,
        bool $inFirstLevelStatement = true,
        array $currentlyAssignedExpressions = []
    ): Scope;
}
