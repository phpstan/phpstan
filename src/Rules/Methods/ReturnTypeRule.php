<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PhpParser\Node;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Rules\FunctionReturnTypeCheck;

class ReturnTypeRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Rules\FunctionReturnTypeCheck */
	private $returnTypeCheck;

	public function __construct(FunctionReturnTypeCheck $returnTypeCheck)
	{
		$this->returnTypeCheck = $returnTypeCheck;
	}

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

		$reflection = null;
		if ($method->getDeclaringClass()->getNativeReflection()->hasMethod($method->getName())) {
			$reflection = $method->getDeclaringClass()->getNativeReflection()->getMethod($method->getName());
		}

		return $this->returnTypeCheck->checkReturnType(
			$scope,
			$method->getReturnType(),
			$node->expr,
			sprintf(
				'Method %s::%s() should return %%s but empty return statement found.',
				$method->getDeclaringClass()->getDisplayName(),
				$method->getName()
			),
			sprintf(
				'Method %s::%s() with return type void returns %%s but should not return anything.',
				$method->getDeclaringClass()->getDisplayName(),
				$method->getName()
			),
			sprintf(
				'Method %s::%s() should return %%s but returns %%s.',
				$method->getDeclaringClass()->getDisplayName(),
				$method->getName()
			),
			$reflection !== null && $reflection->isGenerator(),
			false
		);
	}

}
