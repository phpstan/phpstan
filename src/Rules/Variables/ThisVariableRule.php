<?php declare(strict_types = 1);

namespace PHPStan\Rules\Variables;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;

class ThisVariableRule implements \PHPStan\Rules\Rule
{

	public function getNodeType(): string
	{
		return Variable::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\Variable $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!is_string($node->name) || $node->name !== 'this') {
			return [];
		}

		if ($scope->getClass() === null && !$scope->isInAnonymousClass()) {
			return [
				'Using $this outside a class.',
			];
		}

		$function = $scope->getFunction();
		if (!$function instanceof MethodReflection) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		if ($function->isStatic()) {
			return [
				sprintf(
					'Using $this in static method %s::%s().',
					$scope->getClass() !== null ? $scope->getClass() : 'AnonymousClass',
					$function->getName()
				),
			];
		}

		return [];
	}

}
