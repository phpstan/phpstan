<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PhpParser\Node\Expr\StaticCall;
use PHPStan\Reflection\MethodReflection;

interface StaticMethodTypeSpecifyingExtension
{

	public function getClass(): string;

	public function isStaticMethodSupported(MethodReflection $staticMethodReflection, StaticCall $node, Scope $scope, TypeSpecifierContext $context): bool;

	public function specifyTypes(MethodReflection $staticMethodReflection, StaticCall $node, Scope $scope, TypeSpecifierContext $context): SpecifiedTypes;

}
