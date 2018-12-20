<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

class ClassConstantDeclarationRule implements Rule
{

	public function getNodeType(): string
	{
		return Node\Stmt\ClassConst::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\ClassConst $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!$scope->isInClass()) {
			throw new \PHPStan\ShouldNotHappenException();
		}
		$classReflection = $scope->getClassReflection();

		foreach ($node->consts as $const) {
			$classReflection->getConstant($const->name->name);
		}

		return [];
	}

}
