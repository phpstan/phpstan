<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PhpParser\Node;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\Php\PhpFunctionFromParserNodeReflection;
use PHPStan\Reflection\Php\PhpMethodFromParserNodeReflection;
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

		$function = $scope->getFunction();
		if (
			!($function instanceof PhpFunctionFromParserNodeReflection)
			|| $function instanceof PhpMethodFromParserNodeReflection
		) {
			return [];
		}

		$reflection = null;
		if (function_exists($function->getName())) {
			$reflection = new \ReflectionFunction($function->getName());
		}

		return $this->returnTypeCheck->checkReturnType(
			$scope,
			$function->getReturnType(),
			$node->expr,
			sprintf(
				'Function %s() should return %%s but empty return statement found.',
				$function->getName()
			),
			sprintf(
				'Function %s() with return type void returns %%s but should not return anything.',
				$function->getName()
			),
			sprintf(
				'Function %s() should return %%s but returns %%s.',
				$function->getName()
			),
			$reflection !== null && $reflection->isGenerator(),
			false
		);
	}

}
