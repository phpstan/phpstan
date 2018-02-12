<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Reflection\MethodReflection;

interface MethodTypeSpecifyingExtension
{

	public function getClass(): string;

	public function isMethodSupported(MethodReflection $methodReflection, MethodCall $node, Scope $scope, Context $context): bool;

	public function specifyTypes(MethodReflection $methodReflection, MethodCall $node, Scope $scope, Context $context): SpecifiedTypes;

}
