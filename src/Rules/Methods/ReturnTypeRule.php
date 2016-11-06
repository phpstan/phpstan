<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PhpParser\Node;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\MixedType;

class ReturnTypeRule implements \PHPStan\Rules\Rule
{

	public function getNodeType(): string
	{
		return Return_::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\Return_ $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if ($scope->getFunction() === null) {
			return [];
		}

		if ($scope->isInAnonymousFunction()) {
			return [];
		}

		$method = $scope->getFunction();
		if (!($method instanceof MethodReflection)) {
			return [];
		}

		$returnType = $method->getReturnType();
		$returnValue = $node->expr;
		if ($returnValue === null) {
			if ($returnType instanceof MixedType) {
				return [];
			}

			return [
				sprintf(
					'Method %s::%s() should return %s but empty return statement found.',
					$method->getDeclaringClass()->getName(),
					$method->getName(),
					$returnType->describe()
				),
			];
		}

		$returnValueType = $scope->getType($returnValue);
		if (!$returnType->accepts($returnValueType)) {
			return [
				sprintf(
					'Method %s::%s() should return %s but returns %s.',
					$method->getDeclaringClass()->getName(),
					$method->getName(),
					$returnType->describe(),
					$returnValueType->describe()
				),
			];
		}

		return [];
	}

}
