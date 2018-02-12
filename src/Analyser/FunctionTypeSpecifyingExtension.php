<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Reflection\FunctionReflection;

interface FunctionTypeSpecifyingExtension
{

	public function isFunctionSupported(FunctionReflection $functionReflection, FuncCall $node, Scope $scope, Context $context): bool;

	public function specifyTypes(FunctionReflection $functionReflection, FuncCall $node, Scope $scope, Context $context): SpecifiedTypes;

}
